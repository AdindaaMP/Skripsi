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

        $groups = $activity->groups->map(function ($group) use (&$donatChartDataPerGroup) {
            $questionnaireIds = $group->questionnaires->pluck('id');

            $getSubmitterCount = function ($membersCollection) use ($questionnaireIds) {
                return $membersCollection->filter(function($member) use ($questionnaireIds) {
                    return $member->answers->whereIn('questionnaire_id', $questionnaireIds)->isNotEmpty();
                })->count();
            };

            $pesertaFilled = $getSubmitterCount($group->users);
            $trainerFilled = $getSubmitterCount($group->trainers);
            $proctorFilled = $getSubmitterCount($group->proctors);

            $donatChartDataPerGroup[$group->id] = [
                'peserta' => $pesertaFilled,
                'trainer' => $trainerFilled,
                'proctor' => $proctorFilled,
            ];

            return $group; 
        });

        $totalParticipants = $activity->groups->sum(fn($group) => $group->users->count());

        return view('admin.sertifikasi.show', compact('activity', 'groups', 'totalParticipants', 'donatChartDataPerGroup'));
    }

}
