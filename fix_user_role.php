<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$email = 'mnur2331249@itpln.ac.id';
$user = User::where('email', $email)->first();

if ($user) {
    echo "Sebelum perbaikan:\n";
    echo "Role: " . $user->role . "\n";
    
    // Perbaiki role menjadi 'admin'
    $user->update(['role' => 'admin']);
    
    // Refresh data
    $user->refresh();
    
    echo "\nSetelah perbaikan:\n";
    echo "Role: " . $user->role . "\n";
    echo "User berhasil diperbaiki!\n";
} else {
    echo "User tidak ditemukan!\n";
} 