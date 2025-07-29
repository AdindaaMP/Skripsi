<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Activity;
use App\Models\TrainingGroup;

class DummySertifikasiSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'dummy@example.com'],
            [
                'name' => 'Dummy User',
                'password' => bcrypt('password'),
                'role' => 'user',
            ]
        );

        $activity = Activity::create([
            'name' => 'MOS Dummy',
            'type' => 'mos',
            'description' => 'Kegiatan dummy untuk testing tampilan',
            'registration_start' => now()->subDays(2),
            'registration_end' => now()->addDays(5),
        ]);

        foreach (['Kelompok A', 'Kelompok B'] as $name) {
            $group = $activity->groups()->create(['name' => $name]);

            $group->users()->attach($user->id);
            $group->trainers()->attach($user->id);
            $group->proctors()->attach($user->id);
        }
    }
}
