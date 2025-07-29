<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class TestNIMExtractionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:nim-extraction {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test NIM extraction from email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Testing NIM extraction for email: {$email}");
        
        $nim = $this->extractNIMFromEmail($email);
        $jurusan = $this->getJurusanFromNIM($nim);
        
        if ($nim) {
            $this->info("✅ NIM extracted: {$nim}");
            $this->info("✅ Jurusan: {$jurusan}");
        } else {
            $this->error("❌ Could not extract NIM from email");
        }
        
        return 0;
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