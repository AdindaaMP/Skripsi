<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * Menampilkan halaman dashboard untuk pengguna yang login.
     */
    public function index()
    {
        $user = Auth::user();

        // Cek apakah user perlu mengisi biodata (khusus untuk user/peserta)
        if ($user->role === 'user' && (!$user->nim || !$user->jurusan)) {
            // Coba auto-fill biodata terlebih dahulu
            $this->tryAutoFillBiodata($user);
            
            // Refresh user data dari database
            $user = User::find($user->id);
            
            // Jika masih belum lengkap, redirect ke form biodata
            if (!$user->nim || !$user->jurusan) {
                return redirect()->route('biodata.show');
            }
        }

        try {
            // Pastikan eager loading 'activity', 'trainers', dan 'program.questionnaires'
            $groupsAsParticipant = $user->trainingGroups()->with(['activity', 'trainers', 'program.questionnaires'])->get();
            $groupsAsTrainer     = $user->trainingGroupsAsTrainer()->with(['activity', 'trainers', 'program.questionnaires'])->get();
            $groupsAsProctor     = $user->trainingGroupsAsProctor()->with(['activity', 'trainers', 'program.questionnaires'])->get();

            // Gabungkan semua grup dan pastikan tidak ada duplikasi
            $allGroups = $groupsAsParticipant->merge($groupsAsTrainer)->merge($groupsAsProctor)->unique('id');

            // Tambahkan informasi status pengisian untuk setiap grup
            $groupsWithStatus = $allGroups->map(function ($group) use ($user) {
                // Pastikan program dimuat dengan benar
                if (!$group->program && $group->program_id) {
                    $group->program = \App\Models\Program::find($group->program_id);
                }

                // Hitung status pengisian berdasarkan questionnaires yang ada
                $questionnaireIds = collect();
                if ($group->program && method_exists($group->program, 'questionnaires')) {
                    $questionnaireIds = $group->program->questionnaires->pluck('id');
                }

                // Jika tidak ada questionnaires, anggap sudah diisi
                $hasAnswered = $questionnaireIds->isEmpty() 
                    ? true 
                    : $user->answers()
                        ->whereIn('questionnaire_id', $questionnaireIds)
                        ->where('training_group_id', $group->id)
                        ->exists();

                $group->submission_status = $hasAnswered;
                return $group;
            });

            return view('home', ['groups' => $groupsWithStatus]);
        } catch (\Exception $e) {
            Log::error('Error in HomeController@index: ' . $e->getMessage());
            return view('home', ['groups' => collect()])->with('error', 'Terjadi kesalahan saat memuat data.');
        }
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
