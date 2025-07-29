<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TestMicrosoftLoginCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:microsoft-login {email} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Microsoft login simulation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->argument('name');
        
        $this->info("Testing Microsoft login simulation for: {$email}");
        
        try {
            // Simulasi proses login seperti di AuthController
            $existingUser = User::where('email', $email)->first();
            
            if (!$existingUser) {
                $this->info("Creating new user...");
                $existingUser = User::create([
                    'name' => $name,
                    'email' => $email,
                    'avatar' => null,
                ]);
                
                $existingUser->assignRole('user');
                $this->info("✅ User created successfully");
            } else {
                $this->info("✅ User already exists");
            }
            
            // Test auto-fill biodata
            if (!$existingUser->nim || !$existingUser->jurusan) {
                $this->info("Testing auto-fill biodata...");
                $this->autoFillBiodata($existingUser);
                
                $existingUser->refresh();
                
                if ($existingUser->nim && $existingUser->jurusan) {
                    $this->info("✅ Auto-fill successful: NIM={$existingUser->nim}, Jurusan={$existingUser->jurusan}");
                } else {
                    $this->warn("⚠️ Auto-fill failed");
                }
            } else {
                $this->info("✅ Biodata already filled");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            Log::error('Test Microsoft Login Error: ' . $e->getMessage());
        }
        
        return 0;
    }

    /**
     * Auto-fill biodata berdasarkan email
     */
    private function autoFillBiodata(User $user)
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
     */
    private function extractNIMFromEmail($email)
    {
        if (!str_contains($email, '@itpln.ac.id')) {
            return null;
        }

        $localPart = explode('@', $email)[0];
        
        if (preg_match('/(\d{7,10})/', $localPart, $matches)) {
            $nim = $matches[1];
            
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

        $kodeJurusan = substr($nim, 4, 2);
        
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