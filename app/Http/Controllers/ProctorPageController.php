<?php

namespace App\Http\Controllers;

use App\Models\TrainingGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProctorPageController extends Controller
{
    public function showMonitoring(TrainingGroup $group)
    {
        try {
            if (!$group->proctors->contains(Auth::id())) {
                abort(403, 'Akses Ditolak.');
            }

            $group->load(['questionnaires', 'users.answers', 'activity', 'program']);

            if (!$group->program && $group->program_id) {
                $group->program = \App\Models\Program::find($group->program_id);
            }

            $questionnaireIds = $group->questionnaires->pluck('id');
            $participants = $group->users->map(function ($user) use ($questionnaireIds) {
                $user->submission_status = $user->answers()->whereIn('questionnaire_id', $questionnaireIds)->exists();
                return $user;
            });

            $totalPeserta = $participants->count();
            $sudahMengisi = $participants->where('submission_status', true)->count();
            $belumMengisi = $totalPeserta - $sudahMengisi;
            $tingkatPartisipasi = $totalPeserta > 0 ? round(($sudahMengisi / $totalPeserta) * 100) : 0;

            // Hitung persentase per role untuk chart
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

            return view('proctor.monitoring', [
                'group' => $group,
                'activity' => $group->activity,
                'participants' => $participants,
                'totalPeserta' => $totalPeserta,
                'sudahMengisi' => $sudahMengisi,
                'belumMengisi' => $belumMengisi,
                'tingkatPartisipasi' => $tingkatPartisipasi,
                'trainer_percentage' => $trainer_percentage,
                'proctor_percentage' => $proctor_percentage,
                'peserta_percentage' => $peserta_percentage,
                'final_score' => $final_score,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ProctorPageController@showMonitoring: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat halaman monitoring.');
        }
    }

    public function showKetercapaian()
    {
        try {
            $proctor = Auth::user();
            if ($proctor->role !== 'proctor') {
                abort(403);
            }
            $groups = $proctor->trainingGroupsAsProctor()->with(['questionnaires.answers.user', 'program', 'trainers', 'proctors', 'users'])->get();
            $dataByProgram = [];
            foreach ($groups as $group) {
                if (!$group->program && $group->program_id) {
                    $group->program = \App\Models\Program::find($group->program_id);
                }
                if (empty($group->program)) continue;
                $programName = $group->program->name;
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
            $achievementByProgram = [];
            foreach ($dataByProgram as $program => $scores) {
                $achievementByProgram[$program] = round(collect($scores)->average(), 2);
            }
            return view('proctor.ketercapaian', compact('proctor', 'achievementByProgram'));
        } catch (\Exception $e) {
            Log::error('Error in ProctorPageController@showKetercapaian: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat laporan ketercapaian.');
        }
    }
}
