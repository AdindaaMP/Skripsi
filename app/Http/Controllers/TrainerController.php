<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TrainingGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // Added this import

class TrainerController extends Controller
{
    /**
     * Menampilkan daftar semua trainer.
     */
    public function index()
    {
        // Check if user is admin
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

            // Eager load relasi dengan program
            $groups = $trainer->trainingGroupsAsTrainer()->with(['questionnaires.answers.user', 'program'])->get();

            $dataByProgram = [];
            $suggestionsFromPeserta = [];
            $suggestionsFromProctor = [];

            foreach ($groups as $group) {
                // Pastikan program dimuat dengan benar
                if (!$group->program && $group->program_id) {
                    $group->program = \App\Models\Program::find($group->program_id);
                }
                
                if (empty($group->program)) continue;

                $programName = $group->program->name;
                
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

                $dataByProgram[$programName][] = $percentage;
                
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

            // Hitung rata-rata ketercapaian untuk setiap program
            $achievementByProgram = [];
            foreach ($dataByProgram as $program => $percentages) {
                $achievementByProgram[$program] = round(collect($percentages)->average());
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
