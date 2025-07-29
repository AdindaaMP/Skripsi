<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$email = 'mnur2331249@itpln.ac.id';
$user = User::where('email', $email)->first();

if ($user) {
    echo "User ditemukan:\n";
    echo "ID: {$user->id}\n";
    echo "Nama: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Role: {$user->role}\n";
    echo "NIM: {$user->nim}\n";
    echo "Jurusan: {$user->jurusan}\n";
} else {
    echo "User tidak ditemukan!\n";
} 