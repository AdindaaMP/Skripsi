<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

// Assign role ke mnur2331249@itpln.ac.id
$user1 = User::where('email', 'mnur2331249@itpln.ac.id')->first();
if ($user1) {
    $user1->syncRoles(['admin', 'user']);
    echo "Berhasil assign role admin & user ke {$user1->email}\n";
} else {
    echo "User mnur2331249@itpln.ac.id tidak ditemukan\n";
}

// Assign role ke adinda2131088@itpln.ac.id
$user2 = User::where('email', 'adinda2131088@itpln.ac.id')->first();
if ($user2) {
    $user2->syncRoles(['admin']);
    echo "Berhasil assign role admin ke {$user2->email}\n";
} else {
    echo "User adinda2131088@itpln.ac.id tidak ditemukan\n";
}

echo "Selesai!\n"; 