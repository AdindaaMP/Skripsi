<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Redirect ke Microsoft OAuth
     */
    public function redirectToMicrosoft($role)
    {
        Log::info("Microsoft OAuth: Redirecting to Microsoft with role: {$role}");
        session(['intended_role' => $role]);
        Log::info("Microsoft OAuth: Session intended_role set to: " . session('intended_role'));
        return Socialite::driver('microsoft')->redirect();
    }

    /**
     * Handle callback dari Microsoft OAuth
     */
    public function handleMicrosoftCallback(Request $request)
    {
        try {
            Log::info('Microsoft OAuth: Starting callback process');
            
            $user = Socialite::driver('microsoft')->user();
            $intendedRole = session('intended_role', 'user');
            $email = $user->getEmail();
            
            Log::info("Microsoft OAuth: Email received: {$email}");
            Log::info("Microsoft OAuth: Intended role: {$intendedRole}");
            
            // Validasi email
            if (!$email) {
                Log::error('Microsoft OAuth: Email tidak ditemukan');
                return redirect()->route('welcome')->with('error', 'Email tidak ditemukan dari akun Microsoft.');
            }
            
            // Cari user berdasarkan email dan role yang diinginkan
            $existingUser = User::where('email', $email)
                               ->where('role', $intendedRole)
                               ->first();
            Log::info('Microsoft OAuth: User lookup result: ' . ($existingUser ? 'FOUND' : 'NOT FOUND'));
            
            if (!$existingUser) {
                Log::info("Microsoft OAuth: No user found with email: {$email} and role: {$intendedRole}");
                
                // Cek apakah ada user dengan email yang sama tapi role berbeda
                $userWithSameEmail = User::where('email', $email)->first();
                Log::info('Microsoft OAuth: User with same email lookup: ' . ($userWithSameEmail ? 'FOUND' : 'NOT FOUND'));
                
                if ($userWithSameEmail) {
                    Log::info("Microsoft OAuth: User with same email found with ID: {$userWithSameEmail->id}, role: {$userWithSameEmail->role}");
                    
                    // Jika role berbeda, buat user baru dengan role yang diinginkan
                    if ($userWithSameEmail->role !== $intendedRole) {
                        Log::info("Microsoft OAuth: Creating new user with different role");
                        
                        try {
                            $existingUser = User::create([
                                'name' => $user->getName() ?? 'User',
                                'email' => $email,
                                'role' => $intendedRole,
                                'avatar' => null,
                                'password' => bcrypt(Str::random(16)),
                            ]);
                            
                            Log::info("Microsoft OAuth: New user created with ID: {$existingUser->id}");
                        } catch (\Exception $e) {
                            Log::error("Microsoft OAuth: Failed to create user: " . $e->getMessage());
                            return redirect()->route('welcome')->with('error', 'Gagal membuat akun baru. Silakan coba lagi.');
                        }
                    } else {
                        $existingUser = $userWithSameEmail;
                    }
                } else {
                    Log::info("Microsoft OAuth: Creating new user for email: {$email}");
                    
                    try {
                        // Buat user baru
                        $existingUser = User::create([
                            'name' => $user->getName() ?? 'User',
                            'email' => $email,
                            'role' => $intendedRole,
                            'avatar' => null,
                            'password' => bcrypt(Str::random(16)),
                        ]);
                        
                        Log::info("Microsoft OAuth: New user created with ID: {$existingUser->id}");
                    } catch (\Exception $e) {
                        Log::error("Microsoft OAuth: Failed to create user: " . $e->getMessage());
                        return redirect()->route('welcome')->with('error', 'Gagal membuat akun baru. Silakan coba lagi.');
                    }
                }
            } else {
                Log::info("Microsoft OAuth: Existing user found with ID: {$existingUser->id}");
            }

            // Auto-fill biodata untuk user dengan role 'user'
            if ($intendedRole === 'user') {
                Log::info("Microsoft OAuth: Checking for biodata auto-fill. NIM: {$existingUser->nim}, Jurusan: {$existingUser->jurusan}");
                if (!$existingUser->nim || !$existingUser->jurusan) {
                    Log::info("Microsoft OAuth: Auto-filling biodata for user");
                    $this->autoFillBiodata($existingUser);
                    $existingUser->refresh();
                    Log::info("Microsoft OAuth: Biodata auto-filled. NIM: {$existingUser->nim}, Jurusan: {$existingUser->jurusan}");
                } else {
                    Log::info("Microsoft OAuth: Biodata already filled. NIM: {$existingUser->nim}, Jurusan: {$existingUser->jurusan}");
                }
            }

            Log::info("Microsoft OAuth: Attempting to login user: {$existingUser->email}");
            Auth::login($existingUser);
            
            // Refresh user data setelah auto-fill
            $existingUser->refresh();
            
            Log::info("Microsoft OAuth: User successfully logged in. Role: {$existingUser->role}");
            Log::info("Microsoft OAuth: Auth check: " . (Auth::check() ? 'true' : 'false'));
            Log::info("Microsoft OAuth: Current user ID: " . (Auth::id() ?? 'null'));
            
            // Setelah login, cek undangan group_invites
            $invites = \App\Models\GroupInvite::where('email', $existingUser->email)->get();
            foreach ($invites as $invite) {
                // Tambahkan user ke kelompok jika belum ada
                if (!$existingUser->trainingGroups()->where('training_group_id', $invite->training_group_id)->exists()) {
                    $existingUser->trainingGroups()->attach($invite->training_group_id);
                }
                // Hapus undangan (opsional, atau bisa ditandai sudah join)
                $invite->delete();
            }

            // Redirect berdasarkan role
            if ($intendedRole === 'admin') {
                Log::info("Microsoft OAuth: Redirecting to admin dashboard");
                return redirect()->route('admin.dashboard');
            } elseif ($intendedRole === 'trainer') {
                Log::info("Microsoft OAuth: Redirecting to trainer home");
                return redirect()->route('home.user');
            } elseif ($intendedRole === 'proctor') {
                Log::info("Microsoft OAuth: Redirecting to proctor home");
                return redirect()->route('home.user');
            } else {
                // Untuk user/peserta, hanya redirect ke biodata jika biodata_confirmed == false
                if (!$existingUser->biodata_confirmed) {
                    Log::info("Microsoft OAuth: Redirecting to biodata form for user (first time)");
                    return redirect()->route('biodata.show');
                }
                Log::info("Microsoft OAuth: Redirecting to user home (biodata sudah dikonfirmasi)");
                return redirect()->route('home.user');
            }
        } catch (\Exception $e) {
            Log::error('Microsoft OAuth error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->route('welcome')->with('error', 'Terjadi kesalahan saat login. Silakan coba lagi.');
        }
    }

    // Tambahkan log detail pada autoFillBiodata
    private function autoFillBiodata(User $user)
    {
        $email = $user->email;
        Log::info("AutoFillBiodata: Extracting NIM from email: {$email}");
        // Ekstrak NIM dari email
        $nim = $this->extractNIMFromEmail($email);
        Log::info("AutoFillBiodata: Extracted NIM: " . ($nim ?: 'NULL'));
        if ($nim) {
            $jurusan = $this->getJurusanFromNIM($nim);
            Log::info("AutoFillBiodata: Jurusan from NIM: {$jurusan}");
            $user->update([
                'nim' => $nim,
                'jurusan' => $jurusan,
            ]);
            Log::info("AutoFillBiodata: User updated with NIM and Jurusan");
        } else {
            Log::info("AutoFillBiodata: NIM extraction failed, no update performed");
        }
    }

    /**
     * Ekstrak NIM dari email
     * Format: contoh23@itpln.ac.id -> 202331249
     */
    private function extractNIMFromEmail($email)
    {
        // Cek apakah email dari domain itpln.ac.id
        if (!str_contains($email, '@itpln.ac.id')) {
            return null;
        }

        // Ambil bagian sebelum @
        $localPart = explode('@', $email)[0];
        
        // Cari pola: angka di akhir string
        // Contoh: mnur2331249@itpln.ac.id -> 2331249
        if (preg_match('/(\d{7,10})/', $localPart, $matches)) {
            $nim = $matches[1];
            // Tambahkan '20' di depan jika belum ada
            if (strpos($nim, '20') !== 0) {
                $nim = '20' . $nim;
            }
            // Validasi format NIM
            if (strlen($nim) >= 9) {
                return $nim;
            }
        }
        
        return null;
    }

    /**
     * Menentukan jurusan berdasarkan NIM
     */
    private function getJurusanFromNIM($nim)
    {
        if (strlen($nim) < 7) {
            return 'Jurusan Tidak Diketahui';
        }

        $kodeJurusan = substr($nim, 4, 2); // Ambil 2 digit dari posisi 5 (index 4)
        
        $jurusanMap = [
            '11' => 'S1 Teknik Elektro',
            '12' => 'S1 Teknik Mesin',
            '21' => 'S1 Teknik Sipil',
            '31' => 'S1 Teknik Informatika',
            '71' => 'D3 Teknologi Listrik',
            '72' => 'D3 Teknik Mesin',
            '32' => 'S1 Sistem Informasi',
            '42' => 'S1 Teknik Industri',
            '41' => 'S1 Bisnis Energi',
            '14' => 'S1 Teknik Tenaga Listrik',
            '15' => 'S1 Teknik Sistem Energi',
        ];

        return $jurusanMap[$kodeJurusan] ?? 'Jurusan Tidak Diketahui';
    }
} 