<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Email yang akan dibuat sebagai peserta
$email = 'mnur2331249@itpln.ac.id';
$name = 'Muhammad Nur';
$role = 'user';

// Cek apakah user sudah ada
$existingUser = User::where('email', $email)->first();

if ($existingUser) {
    echo "User dengan email {$email} sudah ada!\n";
    echo "ID: {$existingUser->id}\n";
    echo "Nama: {$existingUser->name}\n";
    echo "Role: {$existingUser->role}\n";
    echo "NIM: {$existingUser->nim}\n";
    echo "Jurusan: {$existingUser->jurusan}\n";
    
    // Update role jika perlu
    if ($existingUser->role !== $role) {
        $existingUser->update(['role' => $role]);
        echo "Role berhasil diubah menjadi: {$role}\n";
    }
} else {
    // Auto-fill biodata berdasarkan email
    $nim = extractNIMFromEmail($email);
    $jurusan = $nim ? getJurusanFromNIM($nim) : null;

    // Buat user baru
    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make('password123'),
        'role' => $role,
        'nim' => $nim,
        'jurusan' => $jurusan,
    ]);

    echo "User berhasil dibuat!\n";
    echo "ID: {$user->id}\n";
    echo "Nama: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Role: {$user->role}\n";
    echo "NIM: {$user->nim}\n";
    echo "Jurusan: {$user->jurusan}\n";
}

function extractNIMFromEmail($email) {
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

function getJurusanFromNIM($nim) {
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