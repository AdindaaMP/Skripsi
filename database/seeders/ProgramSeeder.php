<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Program;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            [
                'name' => 'Microsoft Word 2019',
                'description' => 'Program pelatihan Microsoft Word 2019'
            ],
            [
                'name' => 'Microsoft Excel 2019',
                'description' => 'Program pelatihan Microsoft Excel 2019'
            ],
            [
                'name' => 'Microsoft PowerPoint 2019',
                'description' => 'Program pelatihan Microsoft PowerPoint 2019'
            ],
            [
                'name' => 'Microsoft Azure AI 900',
                'description' => 'Program pelatihan Microsoft Azure AI 900'
            ]
        ];

        foreach ($programs as $program) {
            Program::firstOrCreate(
                ['name' => $program['name']],
                $program
            );
        }
    }
} 