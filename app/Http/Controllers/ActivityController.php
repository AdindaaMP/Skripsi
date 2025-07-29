<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Questionnaire;


class ActivityController extends Controller
{
    /**
     * Menampilkan daftar semua kegiatan.
     */
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
            'groups.trainers',
            'groups.proctors',
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
            // Hitung jawaban berdasarkan training_group_id
            $allAnswersInGroup = Answer::where('training_group_id', $group->id)->get();

            // Hitung jumlah jawaban "Ya" dan "Tidak"
            $yes = $allAnswersInGroup->where('value', '1')->count();
            $no  = $allAnswersInGroup->where('value', '0')->count();

            // Hitung persentase jawaban "Ya"
            $group->percentage = ($yes + $no) > 0 ? round(($yes / ($yes + $no)) * 100) : 0;

            // Info tambahan
            $group->users_count = $group->users->count();
            $group->trainers_count = $group->trainers->count();
            $group->proctors_count = $group->proctors->count();
            $group->trainer_name = optional($group->trainers->first())->name ?? '-';

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
