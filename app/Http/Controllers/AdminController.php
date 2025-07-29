<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Activity;
use App\Models\TrainingGroup;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;


class AdminController extends Controller
{
    public function dashboard(Request $request): View
    {
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

                // Pembobotan per role
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

                $groupData[] = [
                    'group_name'   => $group->name,
                    'trainer_name' => $trainerName,
                    'program_name' => $programName,
                    'percentage'   => $final_score, // pastikan ini hasil pembobotan
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
        $trainers = \App\Models\User::where('role', 'trainer')->get();
        foreach ($trainers as $trainer) {
            // Ambil semua group yang diampu trainer
            $groups = $trainer->trainingGroupsAsTrainer()->with(['program', 'activity', 'questionnaires.answers'])->get();
            $dataByProgram = [];
            foreach ($groups as $group) {
                if (!$group->program && $group->program_id) {
                    $group->program = \App\Models\Program::find($group->program_id);
                }
                if (empty($group->program)) continue;
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
                $dataByProgram[$programName][] = $final_score;
            }
            foreach ($dataByProgram as $programName => $scores) {
                $validScores = array_filter($scores, function($score) { return $score > 0; });
                $avg = !empty($validScores) ? round(array_sum($validScores) / count($validScores), 2) : 0;
                $trainerAchievements[] = [
                    'trainer_name' => $trainer->name,
                    'program_name' => $programName,
                    'average_percentage' => $avg,
                ];
            }
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

                if (!$group->program && $group->program_id) {
                    $group->program = \App\Models\Program::find($group->program_id);
                }

                $programName = $group->program ? $group->program->name : 'General';

                // Hitung ketercapaian dari jawaban yes/no 
                $yesNoQuestions = $group->questionnaires->where('type', 'yes_no');
                $totalAnswerCount = 0;
                $totalPositiveAnswers = 0;
                
                foreach ($yesNoQuestions as $question) {
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

    /**
     * Ekspor evaluasi per kegiatan ke format XLSX (dengan chart).
     */
    public function exportXlsx(Activity $activity)
    {
        if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
            require base_path('vendor/autoload.php');
        }
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ketercapaian');

        // Header
        $headers = [
            'A1' => 'Nama Kelompok',
            'B1' => 'Nama Trainer',
            'C1' => 'Nama Program',
            'D1' => 'Nama Kegiatan',
            'E1' => 'Persentase Ketercapaian',
            'F1' => 'Status',
            'G1' => 'Jumlah Pengisi Evaluasi',
        ];
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $row = 2;
        $dataForChart = [];
        foreach ($activity->groups as $group) {
            $trainer = $group->trainers->first();
            $trainerName = $trainer ? $trainer->name : '-';
            if (!$group->program && $group->program_id) {
                $group->program = \App\Models\Program::find($group->program_id);
            }
            $programName = $group->program ? $group->program->name : 'General';
            $activityName = $activity->name;
            // Hitung ketercapaian
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
            // Status
            $status = $final_score >= 70 ? 'Tercapai' : 'Belum Tercapai';
            // Jumlah pengisi evaluasi (semua role)
            $jumlah_pengisi = 0;
            foreach (['trainers', 'proctors', 'users'] as $role) {
                $userIds = $group->$role->pluck('id');
                if ($userIds->isNotEmpty() && $questionnaireIds->isNotEmpty()) {
                    $jumlah_pengisi += \App\Models\Answer::whereIn('user_id', $userIds)
                        ->whereIn('questionnaire_id', $questionnaireIds)
                        ->where('training_group_id', $group->id)
                        ->distinct('user_id')
                        ->count('user_id');
                }
            }
            $sheet->setCellValue('A'.$row, $group->name);
            $sheet->setCellValue('B'.$row, $trainerName);
            $sheet->setCellValue('C'.$row, $programName);
            $sheet->setCellValue('D'.$row, $activityName);
            $sheet->setCellValue('E'.$row, $final_score);
            $sheet->setCellValue('F'.$row, $status);
            $sheet->setCellValue('G'.$row, $jumlah_pengisi);
            $dataForChart[] = [$group->name, $final_score];
            $row++;
        }

        $dataSeriesLabels = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', "Ketercapaian!\$E$1", null, 1),
        ];
        $xAxisTickValues = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', "Ketercapaian!\$A$2:\$A$".($row-1), null, ($row-2)),
        ];
        $dataSeriesValues = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('Number', "Ketercapaian!\$E$2:\$E$".($row-1), null, ($row-2)),
        ];
        $series = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_BARCHART,
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_CLUSTERED,
            range(0, count($dataSeriesValues)-1),
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues
        );
        $plotArea = new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(null, [$series]);
        $legend = new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_RIGHT, null, false);
        $title = new \PhpOffice\PhpSpreadsheet\Chart\Title('Ketercapaian per Kelompok');
        $chart = new \PhpOffice\PhpSpreadsheet\Chart\Chart(
            'chart1',
            $title,
            $legend,
            $plotArea,
            true,
            0,
            null,
            null
        );
        $chart->setTopLeftPosition('I2');
        $chart->setBottomRightPosition('P20');
        $sheet->addChart($chart);

        // Output file
        $filename = 'ketercapaian_'.$activity->id.'_'.Str::slug($activity->name).'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);
        $writer->save('php://output');
        exit;
    }
}
