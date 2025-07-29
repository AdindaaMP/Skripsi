<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use app\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
            User::create([
            'name' => 'Admin User',
            'email' => 'mnur2331249@itpln.ac.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    }
}