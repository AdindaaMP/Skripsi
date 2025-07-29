<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\Questionnaire;

class QuestionnaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = Program::all();

        foreach ($programs as $program) {
            $questions = $this->getDefaultQuestions($program->name);
            
            foreach ($questions as $index => $question) {
                Questionnaire::firstOrCreate(
                    [
                        'question' => $question['question'],
                        'program_id' => $program->id,
                        'type' => $question['type']
                    ],
                    [
                        'order' => $index + 1,
                        'question' => $question['question'],
                        'type' => $question['type'],
                        'program_id' => $program->id,
                        'activity_id' => null,
                        'training_group_id' => null,
                    ]
                );
            }
        }
    }

    private function getDefaultQuestions($programName)
    {
        $baseQuestions = [
            [
                'question' => 'Apakah materi pelatihan sudah sesuai dengan kebutuhan Anda?',
                'type' => 'yes_no'
            ],
            [
                'question' => 'Apakah trainer menjelaskan materi dengan jelas dan mudah dipahami?',
                'type' => 'yes_no'
            ],
            [
                'question' => 'Apakah fasilitas pelatihan sudah memadai?',
                'type' => 'yes_no'
            ],
            [
                'question' => 'Apakah durasi pelatihan sudah sesuai?',
                'type' => 'yes_no'
            ],
            [
                'question' => 'Saran dan masukan untuk perbaikan pelatihan:',
                'type' => 'text'
            ]
        ];

        // Tambahkan pertanyaan khusus berdasarkan program
        $specificQuestions = [];
        switch ($programName) {
            case 'Microsoft Word 2019':
                $specificQuestions = [
                    [
                        'question' => 'Apakah Anda sudah memahami fitur-fitur dasar Microsoft Word 2019?',
                        'type' => 'yes_no'
                    ],
                    [
                        'question' => 'Apakah Anda sudah bisa membuat dokumen dengan format yang baik?',
                        'type' => 'yes_no'
                    ]
                ];
                break;
            case 'Microsoft Excel 2019':
                $specificQuestions = [
                    [
                        'question' => 'Apakah Anda sudah memahami fungsi-fungsi dasar Excel?',
                        'type' => 'yes_no'
                    ],
                    [
                        'question' => 'Apakah Anda sudah bisa membuat grafik dan tabel?',
                        'type' => 'yes_no'
                    ]
                ];
                break;
            case 'Microsoft PowerPoint 2019':
                $specificQuestions = [
                    [
                        'question' => 'Apakah Anda sudah bisa membuat presentasi yang menarik?',
                        'type' => 'yes_no'
                    ],
                    [
                        'question' => 'Apakah Anda sudah memahami animasi dan transisi?',
                        'type' => 'yes_no'
                    ]
                ];
                break;
            case 'Microsoft Azure AI 900':
                $specificQuestions = [
                    [
                        'question' => 'Apakah Anda sudah memahami konsep dasar AI dan Machine Learning?',
                        'type' => 'yes_no'
                    ],
                    [
                        'question' => 'Apakah Anda sudah familiar dengan layanan Azure AI?',
                        'type' => 'yes_no'
                    ]
                ];
                break;
        }

        return array_merge($baseQuestions, $specificQuestions);
    }
} 