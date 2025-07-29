<?php

namespace App\Http\Controllers;

use App\Models\TrainingGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProctorPageController extends Controller
{
    /**
     * Menampilkan halaman pengawasan peserta untuk sebuah kelompok.
     */
    public function showMonitoring(TrainingGroup $group)
    {
        try {
            if (!$group->proctors->contains(Auth::id())) {
                abort(403, 'Akses Ditolak.');
            }

            $group->load(['questionnaires', 'users.answers', 'activity', 'program']);

            // Pastikan program dimuat dengan benar
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

            return view('proctor.monitoring', [
                'group' => $group,
                'activity' => $group->activity,
                'participants' => $participants,
                'totalPeserta' => $totalPeserta,
                'sudahMengisi' => $sudahMengisi,
                'belumMengisi' => $belumMengisi,
                'tingkatPartisipasi' => $tingkatPartisipasi,
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

            $groups = $proctor->trainingGroupsAsProctor()->with(['questionnaires.answers.user', 'program'])->get();

            $dataByProgram = [];

            foreach ($groups as $group) {
                // Pastikan program dimuat dengan benar
                if (!$group->program && $group->program_id) {
                    $group->program = \App\Models\Program::find($group->program_id);
                }
                
                if (empty($group->program)) continue;

                $programName = $group->program->name;
                
                // Hitung ketercapaian berdasarkan questionnaires yang ada
                $questionnaires = $group->questionnaires->where('type', 'yes_no');
                
                if ($questionnaires->isNotEmpty()) {
                    $totalPositiveAnswers = 0;
                    $totalAnswerCount = 0;
                    
                    foreach ($questionnaires as $questionnaire) {
                        // Filter jawaban berdasarkan training_group_id
                        $answers = $questionnaire->answers()->where('training_group_id', $group->id)->get();
                        if ($answers->isNotEmpty()) {
                            $yesAnswers = $answers->where('value', '1')->count();
                            $totalAnswerCount += $answers->count();
                            $totalPositiveAnswers += $yesAnswers;
                        }
                    }
                    
                    // Hitung persentase ketercapaian
                    $achievement = 0;
                    if ($totalAnswerCount > 0) {
                        $achievement = round(($totalPositiveAnswers / $totalAnswerCount) * 100);
                    }

                    $dataByProgram[$programName]['percentages'][] = $achievement;

                    $textQuestions = $group->questionnaires->where('type', 'text');
                    foreach($textQuestions as $question) {
                        foreach($question->answers as $answer) {
                            if (!empty($answer->value)) {
                                $dataByProgram[$programName]['suggestions'][] = $answer->value;
                            }
                        }
                    }
                }
            }

            $achievementByProgram = [];
            $suggestionsByProgram = [];
            foreach ($dataByProgram as $program => $data) {
                $achievementByProgram[$program] = round(collect($data['percentages'])->average());
                $suggestionsByProgram[$program] = $data['suggestions'] ?? [];
            }

            return view('proctor.ketercapaian', compact('proctor', 'achievementByProgram', 'suggestionsByProgram'));
        } catch (\Exception $e) {
            Log::error('Error in ProctorPageController@showKetercapaian: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat laporan ketercapaian.');
        }
    }
}
