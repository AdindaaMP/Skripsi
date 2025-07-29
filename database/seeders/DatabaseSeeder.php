<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Hanya buat satu pengguna: Administrator
        User::create([
            'name' => 'Adinda Musika',
            'email' => 'adinda.musika@example.com',
            'password' => Hash::make('Password'),
            'role' => 'admin',
            'nim' => '123456789',
            'jurusan' => 'Teknik Informatika',
        ]);
        
        $this->call([
            ProgramSeeder::class,
            QuestionnaireSeeder::class,
        ]);
    }
}
