<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\TrainingGroup;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Answer;
use App\Models\Questionnaire;
use App\Models\GroupInvite;
use Illuminate\Support\Facades\Log;


class TrainingGroupController extends Controller
{
    
    /**
     * Menampilkan form untuk membuat kelompok baru.
     */
    public function create(Activity $sertifikasi)
    {
        $programs = \App\Models\Program::all();
        return view('admin.training_groups.create', compact('sertifikasi', 'programs'));
    }

    /**
     * Menyimpan kelompok baru ke database.
     */
    public function store(Request $request, Activity $sertifikasi)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'kuota' => 'nullable|integer',
                'program_id' => 'required|exists:programs,id'
            ]);

            // Pastikan program ada
            $program = \App\Models\Program::find($validated['program_id']);
            if (!$program) {
                return back()->withErrors(['error' => 'Program yang dipilih tidak ditemukan.'])->withInput();
            }

            $group = $sertifikasi->groups()->create([
                'name' => $validated['name'],
                'kuota' => $validated['kuota'],
                'program_id' => $validated['program_id']
            ]);

            // Load program untuk memastikan relasi tersedia
            $group->load('program');
            
            // Clone questionnaires dari program
            try {
                $group->cloneQuestionnairesFromProgram();
                Log::info("Successfully created training group '{$group->name}' with program '{$program->name}'");
            } catch (\Exception $e) {
                Log::error("Error cloning questionnaires for new group {$group->id}: " . $e->getMessage());
                // Jangan hapus group, hanya log error
            }

            return redirect()->route('admin.sertifikasi.show', $sertifikasi->id)
                             ->with('success', 'Kelompok berhasil dibuat.');
        } catch (\Exception $e) {
            Log::error("Error creating training group: " . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal membuat kelompok: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Menampilkan halaman detail untuk satu kelompok.
     */
    public function show(Request $request, Activity $sertifikasi, TrainingGroup $group)
    {
        // Load semua relasi yang diperlukan
        $group->load([
            'users.answers', 
            'trainers.answers', 
            'proctors.answers', 
            'activity.questionnaires', 
            'program'
        ]);
        
        // Pastikan program dimuat dengan benar
        if (!$group->program && $group->program_id) {
            $group->program = \App\Models\Program::find($group->program_id);
        }
        
        // Ambil questionnaires untuk training group ini
        $questionnaires = Questionnaire::where('training_group_id', $group->id)->orderBy('order')->get();
        $questionnaireIds = $questionnaires->pluck('id');

        // Hitung jawaban berdasarkan training_group_id yang benar
        $allAnswers = Answer::where('training_group_id', $group->id)
            ->whereIn('questionnaire_id', $questionnaireIds)
            ->get();

        // Hitung ketercapaian berdasarkan questionnaires yang ada
        // Pembobotan per role
        $yesNoQuestionnaires = $questionnaires->where('type', 'yes_no');
        $questionnaireIds = $yesNoQuestionnaires->pluck('id');
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
        
        // Hitung persentase ketercapaian (pakai final_score agar konsisten dengan rekap)
        $percentage = $final_score;

        // Pencarian peserta
        $searchTerm = $request->input('search');
        $participantsQuery = $group->users(); 

        if ($searchTerm) {
            $participantsQuery->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      ->orWhere('nim', 'like', "%{$searchTerm}%");
            });
        }
        
        $participants = $participantsQuery->get();
        
        // Hitung status submission untuk setiap peserta
        $participants->map(function ($user) use ($questionnaireIds, $group) {
            $user->submission_status = $user->answers()
                ->whereIn('questionnaire_id', $questionnaireIds)
                ->where('training_group_id', $group->id)
                ->exists();

            return $user;
        });

        // Fungsi untuk menghitung jumlah yang sudah mengisi
        $getSubmitterCount = function ($users) use ($questionnaireIds, $group) {
            return $users->filter(fn($user) =>
                $user->answers()
                    ->whereIn('questionnaire_id', $questionnaireIds)
                    ->where('training_group_id', $group->id)
                    ->exists()
            )->count();
        };

        $pesertaSudahMengisi = $getSubmitterCount($group->users);
        $trainerSudahMengisi = $getSubmitterCount($group->trainers);
        $proctorSudahMengisi = $getSubmitterCount($group->proctors);

        // Peserta yang tersedia untuk ditambahkan
        $allUserIdsInGroup = $group->users->pluck('id');
        $availableUsers = User::where('role', 'user')->whereNotIn('id', $allUserIdsInGroup)->get();

        // Ambil undangan yang belum join
        $invitedEmails = \App\Models\GroupInvite::where('training_group_id', $group->id)->get();

        return view('admin.training_groups.show', [
            'activity' => $sertifikasi, 
            'group' => $group,
            'participants' => $participants, 
            'percentage' => $percentage,
            'pesertaSudahMengisi' => $pesertaSudahMengisi,
            'trainerSudahMengisi' => $trainerSudahMengisi,
            'proctorSudahMengisi' => $proctorSudahMengisi,
            'availableUsers' => $availableUsers, 
            'invitedEmails' => $invitedEmails,
            // pembobotan
            'trainer_percentage' => $trainer_percentage,
            'proctor_percentage' => $proctor_percentage,
            'peserta_percentage' => $peserta_percentage,
            'final_score' => $final_score,
        ]);
    }

    /**
     * Menampilkan form untuk mengedit kelompok.
     */
    public function edit(Activity $sertifikasi, TrainingGroup $group)
    {
        // Load relasi program
        $group->load('program');
        
        // Pastikan program dimuat dengan benar
        if (!$group->program && $group->program_id) {
            $group->program = \App\Models\Program::find($group->program_id);
        }
        
        $programs = \App\Models\Program::all();
        $trainers = User::where('role', 'trainer')->get();
        $proctors = User::where('role', 'proctor')->get();
        return view('admin.training_groups.edit', compact('sertifikasi', 'group', 'trainers', 'proctors', 'programs'));
    }

    /**
     * Menyimpan perubahan pada kelompok.
     */
    public function update(Request $request, Activity $sertifikasi, TrainingGroup $group)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'kuota' => 'required|integer|min:1',
                'program_id' => 'required|exists:programs,id',
                'trainer_id' => 'nullable|exists:users,id',
                'proctor_id' => 'nullable|exists:users,id',
            ]);

            // Pastikan program ada
            $program = \App\Models\Program::find($validated['program_id']);
            if (!$program) {
                return back()->withErrors(['error' => 'Program yang dipilih tidak ditemukan.'])->withInput();
            }

            $oldProgramId = $group->program_id;

            $group->update([
                'name' => $validated['name'],
                'kuota' => $validated['kuota'],
                'program_id' => $validated['program_id'],
            ]);

            $group->trainers()->sync($validated['trainer_id'] ? [$validated['trainer_id']] : []);
            $group->proctors()->sync($validated['proctor_id'] ? [$validated['proctor_id']] : []);

            // Jika program berubah, sync questionnaire
            if ($oldProgramId != $group->program_id) {
                $group->refresh();
                $group->load('program');
                
                try {
                    $group->syncQuestionnairesFromProgram();
                    Log::info("Successfully synced questionnaires for training group '{$group->name}' to program '{$program->name}'");
                } catch (\Exception $e) {
                    Log::error("Error syncing questionnaires for group {$group->id}: " . $e->getMessage());
                    return back()->withErrors(['error' => 'Berhasil memperbarui kelompok, tetapi gagal sinkronisasi kuesioner: ' . $e->getMessage()])->withInput();
                }
            }

            return redirect()->route('admin.sertifikasi.groups.show', ['sertifikasi' => $sertifikasi->id, 'group' => $group->id])
                             ->with('success', 'Kelompok berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error("Error updating training group: " . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal memperbarui kelompok: ' . $e->getMessage()])->withInput();
        }
    }
    
    public function destroy(Activity $sertifikasi, TrainingGroup $group)
    {
        $group->delete();
        return redirect()->route('admin.sertifikasi.show', $sertifikasi->id)->with('success', 'Kelompok berhasil dihapus.');
    }

    /**
     * Menambahkan peserta ke kelompok.
     */
    public function addUser(Request $request, TrainingGroup $group)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        if ($group->users()->count() >= $group->kuota) {
            return back()->with('error', 'Gagal menambahkan peserta, kuota kelompok sudah penuh.');
        }

        $group->users()->syncWithoutDetaching($request->user_id);
        return back()->with('success', 'Peserta berhasil ditambahkan.');
    }

    /**
     * METHOD BARU: Menghapus peserta dari kelompok.
     */
    public function removeUser(TrainingGroup $group, User $user)
    {
        $group->users()->detach($user->id);
        return back()->with('success', 'Peserta berhasil dihapus dari kelompok.');
    }

    /**
     * METHOD BARU: Memperbaiki program_id yang tidak valid
     */
    public function fixProgramIds()
    {
        $firstProgram = \App\Models\Program::first();
        if (!$firstProgram) {
            return response()->json(['error' => 'Tidak ada program tersedia'], 400);
        }

        $fixedCount = 0;
        
        // Ambil semua training group
        $trainingGroups = TrainingGroup::all();
        
        foreach ($trainingGroups as $group) {
            $shouldFix = false;
            
            // Cek apakah program_id null atau 0
            if (!$group->program_id || $group->program_id == 0) {
                $shouldFix = true;
            } else {
                // Cek apakah program dengan ID tersebut ada
                $program = \App\Models\Program::find($group->program_id);
                if (!$program) {
                    $shouldFix = true;
                }
            }
            
            if ($shouldFix) {
                $group->update(['program_id' => $firstProgram->id]);
                $fixedCount++;
                Log::info("Fixed training group {$group->id} program_id to {$firstProgram->id}");
            }
        }

        return response()->json([
            'message' => "Berhasil memperbaiki {$fixedCount} training group",
            'fixed_count' => $fixedCount,
            'first_program_id' => $firstProgram->id,
            'first_program_name' => $firstProgram->name
        ]);
    }

    /**
     * METHOD BARU: Debug data training group dan program
     */
    public function debugProgramData()
    {
        $programs = \App\Models\Program::all();
        $trainingGroups = TrainingGroup::all();
        
        $debugData = [
            'programs' => $programs->map(function($p) {
                return ['id' => $p->id, 'name' => $p->name];
            }),
            'training_groups' => $trainingGroups->map(function($tg) {
                $program = \App\Models\Program::find($tg->program_id);
                return [
                    'id' => $tg->id,
                    'name' => $tg->name,
                    'program_id' => $tg->program_id,
                    'program_exists' => $program ? true : false,
                    'program_name' => $program ? $program->name : 'NOT FOUND'
                ];
            })
        ];
        
        return response()->json($debugData);
    }

    /**
     * METHOD BARU: Refresh relasi program pada semua training group
     */
    public function refreshProgramRelations()
    {
        $trainingGroups = TrainingGroup::all();
        $refreshedCount = 0;
        
        foreach ($trainingGroups as $group) {
            if ($group->program_id) {
                // Force refresh relasi
                $group->refresh();
                $group->load('program');
                $refreshedCount++;
                Log::info("Refreshed training group {$group->id} program relation");
            }
        }
        
        return response()->json([
            'message' => "Berhasil refresh relasi program pada {$refreshedCount} training group",
            'refreshed_count' => $refreshedCount
        ]);
    }

    /**
     * METHOD BARU: Cek dan perbaiki struktur relasi program
     */
    public function checkProgramRelation()
    {
        $results = [];
        
        // Cek tabel programs
        $programs = \App\Models\Program::all();
        $results['programs_count'] = $programs->count();
        $results['programs'] = $programs->map(function($p) {
            return ['id' => $p->id, 'name' => $p->name];
        });
        
        // Cek training groups
        $trainingGroups = TrainingGroup::all();
        $results['training_groups_count'] = $trainingGroups->count();
        $results['training_groups'] = $trainingGroups->map(function($tg) {
            $program = \App\Models\Program::find($tg->program_id);
            $loadResult = null;
            try {
                $tg->load('program');
                $loadResult = $tg->program ? $tg->program->name : 'NULL';
            } catch (\Exception $e) {
                $loadResult = 'ERROR: ' . $e->getMessage();
            }
            
            return [
                'id' => $tg->id,
                'name' => $tg->name,
                'program_id' => $tg->program_id,
                'program_exists' => $program ? true : false,
                'program_name_direct' => $program ? $program->name : 'NOT FOUND',
                'program_name_load' => $loadResult
            ];
        });
        
        return response()->json($results);
    }

    /**
     * METHOD BARU: Perbaiki program berdasarkan nama training group
     */
    public function fixProgramByGroupName()
    {
        $fixedCount = 0;
        $trainingGroups = TrainingGroup::all();
        
        foreach ($trainingGroups as $group) {
            $currentProgram = \App\Models\Program::find($group->program_id);
            $shouldFix = false;
            $targetProgram = null;
            
            // Tentukan program yang seharusnya berdasarkan nama kelompok
            if (stripos($group->name, 'ppt') !== false || stripos($group->name, 'powerpoint') !== false) {
                $targetProgram = \App\Models\Program::where('name', 'like', '%PowerPoint%')->first();
                if ($currentProgram && !str_contains(strtolower($currentProgram->name), 'powerpoint')) {
                    $shouldFix = true;
                }
            } elseif (stripos($group->name, 'word') !== false) {
                $targetProgram = \App\Models\Program::where('name', 'like', '%Word%')->first();
                if ($currentProgram && !str_contains(strtolower($currentProgram->name), 'word')) {
                    $shouldFix = true;
                }
            } elseif (stripos($group->name, 'excel') !== false) {
                $targetProgram = \App\Models\Program::where('name', 'like', '%Excel%')->first();
                if ($currentProgram && !str_contains(strtolower($currentProgram->name), 'excel')) {
                    $shouldFix = true;
                }
            } elseif (stripos($group->name, 'azure') !== false || stripos($group->name, 'ai') !== false) {
                $targetProgram = \App\Models\Program::where('name', 'like', '%Azure%')->first();
                if ($currentProgram && !str_contains(strtolower($currentProgram->name), 'azure')) {
                    $shouldFix = true;
                }
            }
            
            if ($shouldFix && $targetProgram) {
                $group->update(['program_id' => $targetProgram->id]);
                $fixedCount++;
                Log::info("Fixed training group {$group->name} program to {$targetProgram->name}");
            }
        }
        
        return response()->json([
            'message' => "Berhasil memperbaiki {$fixedCount} training group berdasarkan nama",
            'fixed_count' => $fixedCount
        ]);
    }

    /**
     * METHOD BARU: Perbaiki program training group secara manual berdasarkan ID
     */
    public function fixSpecificTrainingGroupProgram($groupId, $programId)
    {
        $group = TrainingGroup::find($groupId);
        $program = \App\Models\Program::find($programId);
        
        if (!$group) {
            return response()->json(['error' => 'Training group tidak ditemukan'], 404);
        }
        
        if (!$program) {
            return response()->json(['error' => 'Program tidak ditemukan'], 404);
        }
        
        $oldProgram = \App\Models\Program::find($group->program_id);
        $group->update(['program_id' => $programId]);
        
        return response()->json([
            'message' => "Berhasil mengubah program training group '{$group->name}' dari '{$oldProgram->name}' ke '{$program->name}'",
            'group_name' => $group->name,
            'old_program' => $oldProgram ? $oldProgram->name : 'Tidak ada',
            'new_program' => $program->name
        ]);
    }

    /**
     * METHOD BARU: Cek dan perbaiki data training group yang bermasalah
     */
    public function checkAndFixTrainingGroups()
    {
        $results = [
            'total_groups' => 0,
            'groups_without_program' => 0,
            'groups_with_invalid_program' => 0,
            'groups_fixed' => 0,
            'errors' => []
        ];

        $trainingGroups = TrainingGroup::all();
        $results['total_groups'] = $trainingGroups->count();

        foreach ($trainingGroups as $group) {
            try {
                // Cek apakah program_id ada
                if (!$group->program_id) {
                    $results['groups_without_program']++;
                    continue;
                }

                // Cek apakah program ada di database
                $program = \App\Models\Program::find($group->program_id);
                if (!$program) {
                    $results['groups_with_invalid_program']++;
                    
                    // Coba perbaiki dengan program pertama yang tersedia
                    $firstProgram = \App\Models\Program::first();
                    if ($firstProgram) {
                        $group->update(['program_id' => $firstProgram->id]);
                        $results['groups_fixed']++;
                        Log::info("Fixed training group {$group->name} program_id from {$group->program_id} to {$firstProgram->id}");
                    }
                }
            } catch (\Exception $e) {
                $results['errors'][] = "Error processing group {$group->id}: " . $e->getMessage();
                Log::error("Error processing training group {$group->id}: " . $e->getMessage());
            }
        }

        return response()->json($results);
    }

    /**
     * METHOD BARU: Cek dan perbaiki questionnaires yang bermasalah
     */
    public function checkAndFixQuestionnaires()
    {
        $results = [
            'total_groups' => 0,
            'groups_without_questionnaires' => 0,
            'groups_with_orphaned_questionnaires' => 0,
            'groups_fixed' => 0,
            'errors' => []
        ];

        $trainingGroups = TrainingGroup::all();
        $results['total_groups'] = $trainingGroups->count();

        foreach ($trainingGroups as $group) {
            try {
                // Cek apakah group memiliki program
                if (!$group->program_id) {
                    continue;
                }

                // Load program
                if (!$group->program) {
                    $group->program = \App\Models\Program::find($group->program_id);
                }

                if (!$group->program) {
                    continue;
                }

                // Cek apakah group memiliki questionnaires
                $questionnaires = Questionnaire::where('training_group_id', $group->id)->get();
                
                if ($questionnaires->isEmpty()) {
                    $results['groups_without_questionnaires']++;
                    
                    // Coba clone questionnaires dari program
                    try {
                        $group->cloneQuestionnairesFromProgram();
                        $results['groups_fixed']++;
                        Log::info("Fixed questionnaires for training group {$group->name}");
                    } catch (\Exception $e) {
                        $results['errors'][] = "Error cloning questionnaires for group {$group->id}: " . $e->getMessage();
                    }
                } else {
                    // Cek apakah ada questionnaires yang orphaned (tidak ada di program)
                    $programQuestionIds = $group->program->questionnaires()
                        ->whereNull('training_group_id')
                        ->pluck('id');
                    
                    $orphanedCount = $questionnaires->whereNotIn('id', $programQuestionIds)->count();
                    
                    if ($orphanedCount > 0) {
                        $results['groups_with_orphaned_questionnaires']++;
                        
                        // Sync questionnaires
                        try {
                            $group->syncQuestionnairesFromProgram();
                            $results['groups_fixed']++;
                            Log::info("Fixed orphaned questionnaires for training group {$group->name}");
                        } catch (\Exception $e) {
                            $results['errors'][] = "Error syncing questionnaires for group {$group->id}: " . $e->getMessage();
                        }
                    }
                }
            } catch (\Exception $e) {
                $results['errors'][] = "Error processing group {$group->id}: " . $e->getMessage();
                Log::error("Error processing training group {$group->id}: " . $e->getMessage());
            }
        }

        return response()->json($results);
    }

    /**
     * METHOD BARU: Cek dan perbaiki semua data yang tidak sinkron
     */
    public function checkAndFixAllData()
    {
        $results = [
            'total_groups' => 0,
            'groups_fixed' => 0,
            'programs_fixed' => 0,
            'questionnaires_fixed' => 0,
            'answers_fixed' => 0,
            'errors' => []
        ];

        try {
            $trainingGroups = TrainingGroup::all();
            $results['total_groups'] = $trainingGroups->count();

            foreach ($trainingGroups as $group) {
                try {
                    // 1. Perbaiki program yang tidak ada
                    if (!$group->program_id) {
                        $firstProgram = \App\Models\Program::first();
                        if ($firstProgram) {
                            $group->update(['program_id' => $firstProgram->id]);
                            $results['programs_fixed']++;
                            Log::info("Fixed missing program for training group {$group->name}");
                        }
                    } elseif (!$group->program) {
                        $program = \App\Models\Program::find($group->program_id);
                        if (!$program) {
                            $firstProgram = \App\Models\Program::first();
                            if ($firstProgram) {
                                $group->update(['program_id' => $firstProgram->id]);
                                $results['programs_fixed']++;
                                Log::info("Fixed invalid program for training group {$group->name}");
                            }
                        }
                    }

                    // 2. Perbaiki questionnaires yang tidak ada
                    $questionnaires = Questionnaire::where('training_group_id', $group->id)->get();
                    if ($questionnaires->isEmpty() && $group->program_id) {
                        try {
                            $group->load('program');
                            if ($group->program) {
                                $group->cloneQuestionnairesFromProgram();
                                $results['questionnaires_fixed']++;
                                Log::info("Fixed missing questionnaires for training group {$group->name}");
                            }
                        } catch (\Exception $e) {
                            $results['errors'][] = "Error cloning questionnaires for group {$group->id}: " . $e->getMessage();
                        }
                    }

                    // 3. Perbaiki answers yang tidak valid
                    $invalidAnswers = Answer::where('training_group_id', $group->id)
                        ->whereNotIn('questionnaire_id', $questionnaires->pluck('id'))
                        ->get();
                    
                    if ($invalidAnswers->count() > 0) {
                        $invalidAnswers->delete();
                        $results['answers_fixed']++;
                        Log::info("Fixed invalid answers for training group {$group->name}");
                    }

                    $results['groups_fixed']++;
                } catch (\Exception $e) {
                    $results['errors'][] = "Error processing group {$group->id}: " . $e->getMessage();
                    Log::error("Error processing training group {$group->id}: " . $e->getMessage());
                }
            }

            return response()->json($results);
        } catch (\Exception $e) {
            $results['errors'][] = "General error: " . $e->getMessage();
            Log::error("Error in checkAndFixAllData: " . $e->getMessage());
            return response()->json($results);
        }
    }

    public function inviteUser(Request $request, $groupId)
    {
        $request->validate([
            'email' => ['required', 'email', 'ends_with:@itpln.ac.id'],
        ]);
        $email = $request->email;
        $group = TrainingGroup::findOrFail($groupId);
        // Cek jika user sudah ada
        $user = \App\Models\User::where('email', $email)->first();
        if ($user) {
            // Jika user sudah ada, langsung attach ke group jika belum
            if (!$group->users()->where('users.id', $user->id)->exists()) {
                $group->users()->attach($user->id);
            }
        } else {
            // Jika belum ada, simpan ke group_invites jika belum ada
            \App\Models\GroupInvite::firstOrCreate([
                'training_group_id' => $group->id,
                'email' => $email,
            ]);
        }
        return back()->with('success', 'Peserta berhasil diundang atau ditambahkan ke kelompok.');
    }
}
