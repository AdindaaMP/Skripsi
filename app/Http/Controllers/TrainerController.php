<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TrainingGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TrainerController extends Controller
{
    public function index()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES KE HALAMAN INI. Role Anda: ' . (Auth::user() ? Auth::user()->role : 'Not authenticated') . '. Role yang diperlukan: admin');
        }
        $trainers = User::where('role', 'trainer')->get();
        return view('admin.trainer.index', compact('trainers'));
    }

    /**
     * Menampilkan form untuk menjadikan user sebagai trainer.
     */
    public function create()
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES KE HALAMAN INI. Role Anda: ' . (Auth::user() ? Auth::user()->role : 'Not authenticated') . '. Role yang diperlukan: admin');
        }

        $users = User::whereNotIn('role', ['admin', 'trainer'])->get();
        return view('admin.trainer.create', compact('users'));
    }

    /**
     * Menyimpan user yang dipilih sebagai trainer baru.
     */
    public function store(Request $request)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES KE HALAMAN INI. Role Anda: ' . (Auth::user() ? Auth::user()->role : 'Not authenticated') . '. Role yang diperlukan: admin');
        }

        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            $user = User::find($validated['user_id']);
            
            if (!$user) {
                return back()->withErrors(['error' => 'User tidak ditemukan.'])->withInput();
            }

            if ($user->role === 'trainer') {
                return back()->withErrors(['error' => 'User ini sudah menjadi trainer.'])->withInput();
            }

            $user->role = 'trainer'; 
            $user->save();

            return redirect()->route('admin.trainer.index')->with('success', 'User berhasil dijadikan trainer.');
        } catch (\Exception $e) {
            Log::error('Error in TrainerController@store: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menjadikan user sebagai trainer: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Menampilkan halaman detail seorang trainer.
     */
    public function show(User $trainer)
    {
        try {
            if ($trainer->role !== 'trainer') {
                abort(404);
            }
            $groups = $trainer->trainingGroupsAsTrainer()->with(['questionnaires.answers.user', 'program', 'trainers', 'proctors', 'users'])->get();
            $dataByProgram = [];
            $suggestionsFromPeserta = [];
            $suggestionsFromProctor = [];
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
                $dataByProgram[$programName][] = $final_score;
                
                // Ambil semua saran dari pertanyaan bertipe 'text'
                $textQuestions = $group->questionnaires->where('type', 'text');

                foreach ($textQuestions as $question) {
                    foreach ($question->answers as $answer) {
                        if (!empty($answer->value) && $answer->user) {
                            $role = $answer->user->role;
                            if ($role === 'user') {
                                $suggestionsFromPeserta[$programName][] = $answer->value;
                            } elseif ($role === 'proctor') {
                                $suggestionsFromProctor[$programName][] = $answer->value;
                            }
                        }
                    }
                }
            }
            $achievementByProgram = [];
            foreach ($dataByProgram as $program => $scores) {
                $achievementByProgram[$program] = round(collect($scores)->average(), 2);
            }
            return view('admin.trainer.show', compact(
                'trainer',
                'achievementByProgram',
                'suggestionsFromPeserta',
                'suggestionsFromProctor'
            ));
        } catch (\Exception $e) {
            Log::error('Error in TrainerController@show: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data trainer.');
        }
    }

    /**
     * Menampilkan form untuk mengedit bio data trainer.
     */
    public function edit(User $trainer)
    {
        return view('admin.trainer.edit', compact('trainer'));
    }

    /**
     * Memperbarui bio data trainer.
     */
    public function update(Request $request, User $trainer)
    {
        try {
            if ($trainer->role !== 'trainer') {
                abort(404, 'User ini bukan trainer.');
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $trainer->id,
                'avatar' => 'nullable|string|max:255', 
            ]);

            $trainer->update($validated);

            return redirect()->route('admin.trainer.show', $trainer->id)->with('success', 'Data trainer berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error in TrainerController@update: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal memperbarui data trainer: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Menghapus seorang trainer (mengubah rolenya kembali menjadi user).
     */
    public function destroy(User $trainer)
    {
        try {
            if ($trainer->role !== 'trainer') {
                abort(404, 'User ini bukan trainer.');
            }

            $trainer->role = 'user';
            $trainer->save();

            return redirect()->route('admin.trainer.index')->with('success', 'Trainer berhasil dihapus (role diubah menjadi user).');
        } catch (\Exception $e) {
            Log::error('Error in TrainerController@destroy: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menghapus trainer: ' . $e->getMessage()]);
        }
    }
}
