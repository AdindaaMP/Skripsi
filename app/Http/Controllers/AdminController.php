<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Activity;
use App\Models\TrainingGroup;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;


class AdminController extends Controller
{
    /**
     * Menampilkan dashboard admin dengan ringkasan kegiatan & ketercapaian per kelompok.
     */
    public function dashboard(Request $request): View
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES KE HALAMAN INI. Role Anda: ' . (Auth::user() ? Auth::user()->role : 'Not authenticated') . '. Role yang diperlukan: admin');
        }

        logger('Role user: ' . Auth::user()->role);


        $selectedYear = $request->year;

        $availableYears = Activity::selectRaw('YEAR(registration_start) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $activities = Activity::with(['groups.trainers', 'groups.questionnaires.answers', 'groups.users', 'groups.program'])
            ->when($selectedYear, function ($query) use ($selectedYear) {
                $query->whereYear('registration_start', $selectedYear);
            })
            ->get();

        $activitySummaries = [];

        foreach ($activities as $activity) {
            $groupData = [];

            foreach ($activity->groups as $group) {
                $trainer = $group->trainers->first();
                $trainerName = $trainer ? $trainer->name : 'Belum ada trainer';

                // Pastikan program dimuat dengan benar
                if (!$group->program && $group->program_id) {
                    $group->program = \App\Models\Program::find($group->program_id);
                }

                $programName = $group->program ? $group->program->name : 'General';

                // Hitung ketercapaian dari jawaban yes/no saja
                $yesNoQuestions = $group->questionnaires->where('type', 'yes_no');
                $totalAnswerCount = 0;
                $totalPositiveAnswers = 0;
                
                foreach ($yesNoQuestions as $question) {
                    // Filter jawaban berdasarkan training_group_id
                    $answers = $question->answers()->where('training_group_id', $group->id)->get();
                    if ($answers->isNotEmpty()) {
                        $yesAnswers = $answers->where('value', '1')->count();
                        $totalAnswerCount += $answers->count();
                        $totalPositiveAnswers += $yesAnswers;
                    }
                }
                
                $percentage = $totalAnswerCount > 0 ? round(($totalPositiveAnswers / $totalAnswerCount) * 100) : 0;

                $groupData[] = [
                    'group_name'   => $group->name,
                    'trainer_name' => $trainerName,
                    'program_name' => $programName,
                    'percentage'   => $percentage,
                ];
            }

            $activitySummaries[] = [
                'activity_id'   => $activity->id,
                'activity_name' => $activity->name,
                'groups'        => $groupData,
            ];
        }

        // Tambahkan data trainer dengan ketercapaian per program
        $trainerAchievements = [];
        
        foreach ($activities as $activity) {
            foreach ($activity->groups as $group) {
                $trainer = $group->trainers->first();
                if (!$trainer) continue;
                
                // Pastikan program dimuat dengan benar
                if (!$group->program && $group->program_id) {
                    $group->program = \App\Models\Program::find($group->program_id);
                }
                
                $programName = $group->program ? $group->program->name : 'General';
                
                // Hitung ketercapaian dari jawaban yes/no saja
                $yesNoQuestions = $group->questionnaires->where('type', 'yes_no');
                $totalAnswerCount = 0;
                $totalPositiveAnswers = 0;
                
                foreach ($yesNoQuestions as $question) {
                    // Filter jawaban berdasarkan training_group_id
                    $answers = $question->answers()->where('training_group_id', $group->id)->get();
                    if ($answers->isNotEmpty()) {
                        $yesAnswers = $answers->where('value', '1')->count();
                        $totalAnswerCount += $answers->count();
                        $totalPositiveAnswers += $yesAnswers;
                    }
                }
                
                $percentage = $totalAnswerCount > 0 ? round(($totalPositiveAnswers / $totalAnswerCount) * 100) : 0;
                
                // Kelompokkan berdasarkan trainer dan program
                $key = $trainer->name . '|' . $programName;
                if (!isset($trainerAchievements[$key])) {
                    $trainerAchievements[$key] = [
                        'trainer_name' => $trainer->name,
                        'program_name' => $programName,
                        'percentages' => [],
                        'groups' => []
                    ];
                }
                
                $trainerAchievements[$key]['percentages'][] = $percentage;
                $trainerAchievements[$key]['groups'][] = $group->name;
            }
        }
        
        // Hitung rata-rata ketercapaian per trainer-program
        foreach ($trainerAchievements as $key => &$data) {
            $data['average_percentage'] = !empty($data['percentages']) ? round(array_sum($data['percentages']) / count($data['percentages'])) : 0;
        }

        return view('admin.dashboard', compact('activitySummaries', 'availableYears', 'trainerAchievements'));
    }

    /**
     * Ekspor evaluasi per kegiatan ke format CSV.
     */
    public function exportCsv(Activity $activity): StreamedResponse
    {
        $filename = 'evaluasi_' . Str::slug($activity->name) . '.csv';

        $response = new StreamedResponse(function () use ($activity) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Kelompok', 'Trainer', 'Program', 'Ketercapaian (%)']);

            foreach ($activity->groups as $group) {
                if ($group->questionnaires->isEmpty()) {
                    continue;
                }

                $trainer = $group->trainers->first();
                $trainerName = $trainer ? $trainer->name : '-';

                // Pastikan program dimuat dengan benar
                if (!$group->program && $group->program_id) {
                    $group->program = \App\Models\Program::find($group->program_id);
                }

                $programName = $group->program ? $group->program->name : 'General';

                // Hitung ketercapaian dari jawaban yes/no saja
                $yesNoQuestions = $group->questionnaires->where('type', 'yes_no');
                $totalAnswerCount = 0;
                $totalPositiveAnswers = 0;
                
                foreach ($yesNoQuestions as $question) {
                    // Filter jawaban berdasarkan training_group_id
                    $answers = $question->answers()->where('training_group_id', $group->id)->get();
                    if ($answers->isNotEmpty()) {
                        $yesAnswers = $answers->where('value', '1')->count();
                        $totalAnswerCount += $answers->count();
                        $totalPositiveAnswers += $yesAnswers;
                    }
                }
                
                $percentage = $totalAnswerCount > 0 ? round(($totalPositiveAnswers / $totalAnswerCount) * 100) : 0;

                fputcsv($handle, [
                    $group->name,
                    $trainerName,
                    $programName,
                    $percentage
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
