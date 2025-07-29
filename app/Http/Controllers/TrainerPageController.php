<?php

namespace App\Http\Controllers;

use App\Models\TrainingGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Answer;

class TrainerPageController extends Controller
{
    /**
     * Menampilkan halaman hasil evaluasi dan saran untuk sebuah kelompok.
     */
    public function showResults(TrainingGroup $group)
    {
        try {
            // Validasi akses trainer
            if (!$group->trainers->contains(Auth::id())) {
                abort(403, 'Akses Ditolak. Anda tidak memiliki akses ke kelompok ini.');
            }

            // Load data dengan eager loading untuk performa yang lebih baik
            $group->load(['questionnaires.answers.user', 'activity', 'program', 'trainers', 'proctors', 'users']);

            // Pastikan program dimuat dengan benar
            if (!$group->program && $group->program_id) {
                $group->program = \App\Models\Program::find($group->program_id);
            }

            // Filter pertanyaan berdasarkan tipe
            $yesNoQuestions = $group->questionnaires->where('type', 'yes_no');
            $textQuestions = $group->questionnaires->where('type', 'text');
            
            // Debug: Log the number of questions
            Log::info('TrainerPageController: Processing group', [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'yes_no_questions_count' => $yesNoQuestions->count(),
                'text_questions_count' => $textQuestions->count()
            ]);
            
            // Hitung statistik untuk pertanyaan ya/tidak
            $questionStats = $yesNoQuestions->map(function ($question) use ($group) {
                // Filter jawaban berdasarkan training_group_id
                $answers = $question->answers()->where('training_group_id', $group->id)->get();
                $totalAnswers = $answers->count();
                
                // Hitung jawaban "ya" berdasarkan string '1' (karena value adalah text)
                $yesAnswers = $answers->where('value', '1')->count();
                
                $percentage = $totalAnswers > 0 ? round(($yesAnswers / $totalAnswers) * 100) : 0;
                
                // Debug: Log each question's stats
                Log::info('Question stats calculated', [
                    'question_id' => $question->id,
                    'question_text' => substr($question->question, 0, 50) . '...',
                    'total_answers' => $totalAnswers,
                    'yes_answers' => $yesAnswers,
                    'percentage' => $percentage
                ]);
                
                return [
                    'question' => $question->question,
                    'percentage' => $percentage,
                    'total_answers' => $totalAnswers,
                    'yes_answers' => $yesAnswers,
                ];
            })->values();

            // Debug: Log final stats
            Log::info('Final question stats', [
                'total_questions' => $questionStats->count(),
                'average_percentage' => $questionStats->avg('percentage'),
                'stats_data' => $questionStats->toArray()
            ]);

            // Kumpulkan saran dari pertanyaan teks
            $suggestionsFromPeserta = [];
            $suggestionsFromProctor = [];

            foreach ($textQuestions as $question) {
                foreach ($question->answers as $answer) {
                    // Cek jika jawaban tidak kosong dan user ada
                    if (!empty(trim($answer->value)) && $answer->user) {
                        if ($answer->user->role === 'user') {
                            $suggestionsFromPeserta[] = trim($answer->value);
                        } elseif ($answer->user->role === 'proctor') {
                            $suggestionsFromProctor[] = trim($answer->value);
                        }
                    }
                }
            }

            // Hapus duplikasi saran
            $suggestionsFromPeserta = array_unique($suggestionsFromPeserta);
            $suggestionsFromProctor = array_unique($suggestionsFromProctor);

            $questionnaireIds = $yesNoQuestions->pluck('id');
            $calc_percentage = function($role, $group, $questionnaireIds) {
                $userIds = $group->$role->pluck('id');
                if ($userIds->isEmpty() || $questionnaireIds->isEmpty()) return 0;
                $answers = \App\Models\Answer::whereIn('user_id', $userIds)
                    ->whereIn('questionnaire_id', $questionnaireIds)
                    ->where('training_group_id', $group->id)
                    ->get();
                $yes = $answers->where('value', '1')->count();
                $no = $answers->whereIn('value', ['0', 0])->count();
                $total = $yes + $no;
                return $total > 0 ? round(($yes / $total) * 100, 2) : 0;
            };
            $trainer_percentage = $calc_percentage('trainers', $group, $questionnaireIds);
            $proctor_percentage = $calc_percentage('proctors', $group, $questionnaireIds);
            $peserta_percentage = $calc_percentage('users', $group, $questionnaireIds);
            $final_score = round(
                ($trainer_percentage * 0.35) +
                ($proctor_percentage * 0.25) +
                ($peserta_percentage * 0.40), 2
            );

            return view('trainer.results', [
                'group' => $group,
                'activity' => $group->activity,
                'questionStats' => $questionStats,
                'suggestionsFromPeserta' => collect($suggestionsFromPeserta), 
                'suggestionsFromProctor' => collect($suggestionsFromProctor), 
                'trainer_percentage' => $trainer_percentage,
                'proctor_percentage' => $proctor_percentage,
                'peserta_percentage' => $peserta_percentage,
                'final_score' => $final_score,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TrainerPageController@showResults: ' . $e->getMessage(), [
                'group_id' => $group->id ?? 'unknown',
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Terjadi kesalahan saat memuat data hasil evaluasi. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan halaman profil trainer dengan rekap ketercapaian per program.
     */
    public function profile()
    {
        try {
            $trainer = Auth::user();

            // Validasi role trainer
            if ($trainer->role !== 'trainer') {
                abort(403, 'Akses Ditolak. Halaman ini hanya untuk trainer.');
            }

            // Load training groups dengan eager loading
            $trainingGroups = $trainer->trainingGroupsAsTrainer()
                ->with(['questionnaires.answers.user', 'program', 'activity', 'trainers', 'proctors', 'users'])
                ->get();

            $dataByProgram = [];

            foreach ($trainingGroups as $group) {
                // Pastikan program dan activity dimuat dengan benar
                if (!$group->program && $group->program_id) {
                    $group->program = \App\Models\Program::find($group->program_id);
                }
                if (!$group->activity && $group->activity_id) {
                    $group->activity = \App\Models\Activity::find($group->activity_id);
                }
                
                // Skip jika tidak ada program atau activity
                if (empty($group->program) || empty($group->activity)) {
                    Log::warning("Training group {$group->id} tidak memiliki program atau activity yang valid");
                    continue;
                }
                
                $programName = $group->program->name;
                
                // Hitung ketercapaian berdasarkan questionnaires ya/no
                $yesNoQuestions = $group->questionnaires->where('type', 'yes_no');
                $questionnaireIds = $yesNoQuestions->pluck('id');
                $calc_percentage = function($role, $group, $questionnaireIds) {
                    $userIds = $group->$role->pluck('id');
                    if ($userIds->isEmpty() || $questionnaireIds->isEmpty()) return 0;
                    $answers = \App\Models\Answer::whereIn('user_id', $userIds)
                        ->whereIn('questionnaire_id', $questionnaireIds)
                        ->where('training_group_id', $group->id)
                        ->get();
                    $yes = $answers->where('value', '1')->count();
                    $no = $answers->whereIn('value', ['0', 0])->count();
                    $total = $yes + $no;
                    return $total > 0 ? round(($yes / $total) * 100, 2) : 0;
                };
                $trainer_percentage = $calc_percentage('trainers', $group, $questionnaireIds);
                $proctor_percentage = $calc_percentage('proctors', $group, $questionnaireIds);
                $peserta_percentage = $calc_percentage('users', $group, $questionnaireIds);
                $final_score = round(
                    ($trainer_percentage * 0.35) +
                    ($proctor_percentage * 0.25) +
                    ($peserta_percentage * 0.40), 2
                );

                if (!isset($dataByProgram[$programName])) {
                    $dataByProgram[$programName] = [];
                }
                $dataByProgram[$programName][] = $final_score;
            }

            // Hitung rata-rata ketercapaian per program
            $achievementByProgram = [];
            foreach ($dataByProgram as $program => $scores) {
                if (!empty($scores)) {
                    $achievementByProgram[$program] = round(collect($scores)->average(), 2);
                }
            }

            return view('trainer.profile', compact('trainer', 'achievementByProgram'));
        } catch (\Exception $e) {
            Log::error('Error in TrainerPageController@profile: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Terjadi kesalahan saat memuat profil trainer. Silakan coba lagi.');
        }
    }
}
