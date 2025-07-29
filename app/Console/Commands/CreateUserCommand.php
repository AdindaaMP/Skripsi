<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {email} {--name=} {--role=user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user with specified email and role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->option('name') ?: 'User';
        $role = $this->option('role');

        // Cek apakah user sudah ada
        $existingUser = User::where('email', $email)->first();
        
        if ($existingUser) {
            $this->warn("User dengan email {$email} sudah ada!");
            $this->info("ID: {$existingUser->id}");
            $this->info("Nama: {$existingUser->name}");
            $this->info("Role: {$existingUser->role}");
            $this->info("NIM: {$existingUser->nim}");
            $this->info("Jurusan: {$existingUser->jurusan}");
            
            // Tanya apakah ingin update role
            if ($this->confirm('Apakah Anda ingin mengubah role user ini?')) {
                $existingUser->update(['role' => $role]);
                $this->info("Role berhasil diubah menjadi: {$role}");
            }
            
            return;
        }

        // Auto-fill biodata berdasarkan email
        $nim = $this->extractNIMFromEmail($email);
        $jurusan = $nim ? $this->getJurusanFromNIM($nim) : null;

        // Buat user baru
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'), // Password default
            'role' => $role,
            'nim' => $nim,
            'jurusan' => $jurusan,
        ]);

        $this->info("User berhasil dibuat!");
        $this->info("ID: {$user->id}");
        $this->info("Nama: {$user->name}");
        $this->info("Email: {$user->email}");
        $this->info("Role: {$user->role}");
        $this->info("NIM: {$user->nim}");
        $this->info("Jurusan: {$user->jurusan}");
    }

    /**
     * Ekstrak NIM dari email
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