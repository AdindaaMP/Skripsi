<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckBiodata
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Cek apakah user perlu mengisi biodata (khusus untuk user/peserta)
            if ($user->role === 'user' && (!$user->nim || !$user->jurusan)) {
                // Coba auto-fill biodata terlebih dahulu
                $this->tryAutoFillBiodata($user);
                
                // Refresh user data dari database
                $user = User::find($user->id);
                
                // Jika masih belum lengkap, redirect ke form biodata
                if (!$user->nim || !$user->jurusan) {
                    // Jangan redirect jika sudah di halaman biodata
                    if (!$request->is('biodata*')) {
                        return redirect()->route('biodata.show');
                    }
                }
            }
        }

        return $next($request);
    }

    /**
     * Coba auto-fill biodata berdasarkan email
     */
    private function tryAutoFillBiodata(User $user)
    {
        $email = $user->email;
        
        // Ekstrak NIM dari email
        $nim = $this->extractNIMFromEmail($email);
        
        if ($nim) {
            $jurusan = $this->getJurusanFromNIM($nim);
            
            $user->update([
                'nim' => $nim,
                'jurusan' => $jurusan,
            ]);
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
            
            // Validasi format NIM
            if (strlen($nim) >= 7) {
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