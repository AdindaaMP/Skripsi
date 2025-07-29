<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\TrainingGroup;
use App\Models\User;

class SertifikasiController extends Controller
{
    public function index()
    {
        $certifications = Activity::all();
        return view('admin.sertifikasi.index', compact('certifications'));
    }

    public function create()
    {
        return view('admin.sertifikasi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'type'              => 'required|in:mos,mcf,mtcna',
            'description'       => 'required|string',
            'registration_start' => 'required|date',
            'registration_end'  => 'required|date|after:registration_start',
        ]);

        Activity::create($request->only([
            'name',
            'type',
            'description',
            'registration_start',
            'registration_end',
        ]));

        return redirect()
            ->route('admin.sertifikasi.index')
            ->with('success', 'Sertifikasi berhasil ditambahkan!');
    }

    public function show($id)
    {
        $activity = Activity::with([
            'groups.trainers.answers', 
            'groups.proctors.answers', 
            'groups.users.answers',    
            'groups.questionnaires'    
        ])->findOrFail($id);

        $donatChartDataPerGroup = [];

        $groups = $activity->groups->map(function ($group) {
            $questionnaireIds = $group->questionnaires->where('type', 'yes_no')->pluck('id');

            // Helper untuk hitung persentase per role
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

            return [
                'id' => $group->id,
                'name' => $group->name,
                'program' => $group->program,
                'trainers' => $group->trainers,
                'proctors' => $group->proctors,
                'users_count' => $group->users_count,
                'trainer_name' => $group->trainer_name,
                'kuota' => $group->kuota,
                'trainer_percentage' => $trainer_percentage,
                'proctor_percentage' => $proctor_percentage,
                'peserta_percentage' => $peserta_percentage,
                'final_score' => $final_score,
                'percentage' => $final_score, // untuk chart dan tabel
            ];
        });

        $totalParticipants = $activity->groups->sum(fn($group) => $group->users->count());

        return view('admin.sertifikasi.show', compact('activity', 'groups', 'totalParticipants', 'donatChartDataPerGroup'));
    }

}
