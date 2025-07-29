<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Questionnaire;


class ActivityController extends Controller
{
    public function index()
    {
        $certifications = Activity::with('groups.users')->get()->map(function ($activity) {
            $activity->total_users = $activity->groups->sum(fn($group) => $group->users->count());
            return $activity;
        });
        return view('admin.sertifikasi.index', compact('certifications'));
    }

    /**
     * Menampilkan form untuk membuat kegiatan baru.
     */
    public function create()
    {
        return view('admin.sertifikasi.create');
    }

    /**
     * Menyimpan kegiatan baru yang dibuat dari form.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'type' => 'required|string|max:255',
        'description' => 'required|string',
        'logo' => 'nullable|string',
        'registration_start' => 'required|date',
        'registration_end' => 'required|date|after:registration_start',
    ]);

    $activity = Activity::create($validated);

    return redirect()->route('admin.sertifikasi.index')
                     ->with('success', 'Kegiatan baru berhasil ditambahkan beserta pertanyaan evaluasi.');
}

    /**
     * Menampilkan detail satu kegiatan beserta kelompoknya.
     */
    public function show(Activity $sertifikasi)
    {
        $sertifikasi->load([
            'groups.users.answers',
            'groups.trainers.answers',
            'groups.proctors.answers',
            'groups.program.questionnaires',
            'groups.program',
            'questionnaires'
        ]);

        // Pastikan program dimuat dengan benar untuk semua groups
        $sertifikasi->groups->each(function($group) {
            if (!$group->program && $group->program_id) {
                $group->program = \App\Models\Program::find($group->program_id);
            }
        });

        $totalParticipants = $sertifikasi->groups->sum(fn($group) => $group->users->count());

        $groups = $sertifikasi->groups->map(function ($group) {
            $questionnaires = $group->questionnaires ?? collect();
            if ($questionnaires->isEmpty() && $group->program) {
                $questionnaires = $group->program->questionnaires ?? collect();
            }
            $yesNoQuestionnaires = $questionnaires->where('type', 'yes_no');
            $questionnaireIds = $yesNoQuestionnaires->pluck('id');

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

            // Info tambahan
            $group->users_count = $group->users->count();
            $group->trainers_count = $group->trainers->count();
            $group->proctors_count = $group->proctors->count();
            $group->trainer_name = optional($group->trainers->first())->name ?? '-';
            $group->percentage = $final_score;
            $group->final_score = $final_score;
            $group->trainer_percentage = $trainer_percentage;
            $group->proctor_percentage = $proctor_percentage;
            $group->peserta_percentage = $peserta_percentage;

            // Hitung jumlah orang yang sudah mengisi per role
            $filled_users = $group->users->filter(function($user) use ($questionnaireIds, $group) {
                return \App\Models\Answer::where('user_id', $user->id)
                    ->whereIn('questionnaire_id', $questionnaireIds)
                    ->where('training_group_id', $group->id)
                    ->exists();
            })->count();
            $filled_trainers = $group->trainers->filter(function($user) use ($questionnaireIds, $group) {
                return \App\Models\Answer::where('user_id', $user->id)
                    ->whereIn('questionnaire_id', $questionnaireIds)
                    ->where('training_group_id', $group->id)
                    ->exists();
            })->count();
            $filled_proctors = $group->proctors->filter(function($user) use ($questionnaireIds, $group) {
                return \App\Models\Answer::where('user_id', $user->id)
                    ->whereIn('questionnaire_id', $questionnaireIds)
                    ->where('training_group_id', $group->id)
                    ->exists();
            })->count();
            $group->filled_users = $filled_users;
            $group->filled_trainers = $filled_trainers;
            $group->filled_proctors = $filled_proctors;
            return $group;
        });

        return view('admin.sertifikasi.show', [
            'activity' => $sertifikasi,
            'groups' => $groups,
            'totalParticipants' => $totalParticipants
        ]);
    }



    /**
     * Menampilkan form untuk mengedit kegiatan.
     */
    public function edit(Activity $sertifikasi)
    {
        return view('admin.sertifikasi.edit', ['certification' => $sertifikasi]);
    }

    /**
     * Mengupdate data kegiatan di database.
     */
    public function update(Request $request, Activity $sertifikasi)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255', 'type' => 'required|string|max:255',
            'description' => 'required|string', 'logo' => 'nullable|string',
            'registration_start' => 'required|date', 'registration_end' => 'required|date|after:registration_start',
        ]);
        $sertifikasi->update($validated);
        return redirect()->route('admin.sertifikasi.show', $sertifikasi->id)->with('success', 'Kegiatan berhasil diperbarui.');
    }

    /**
     * Menghapus kegiatan.
     */
    public function destroy(Activity $sertifikasi)
    {
        $sertifikasi->delete();
        return redirect()->route('admin.sertifikasi.index')->with('success', 'Kegiatan berhasil dihapus.');
    }

    
}
