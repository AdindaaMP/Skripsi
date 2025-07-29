<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProctorController extends Controller
{
    /**
     * Menampilkan daftar semua proctor.
     */
    public function index()
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES KE HALAMAN INI. Role Anda: ' . (Auth::user() ? Auth::user()->role : 'Not authenticated') . '. Role yang diperlukan: admin');
        }

        $proctors = User::where('role', 'proctor')->get();
        return view('admin.proctor.index', compact('proctors'));
    }

    /**
     * Menampilkan form untuk menjadikan user sebagai proctor.
     */
    public function create()
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES KE HALAMAN INI. Role Anda: ' . (Auth::user() ? Auth::user()->role : 'Not authenticated') . '. Role yang diperlukan: admin');
        }

        $users = User::whereNotIn('role', ['admin', 'trainer', 'proctor'])->get();
        return view('admin.proctor.create', compact('users'));
    }

    /**
     * Menyimpan user yang dipilih sebagai proctor baru.
     */
    public function store(Request $request)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES KE HALAMAN INI. Role Anda: ' . (Auth::user() ? Auth::user()->role : 'Not authenticated') . '. Role yang diperlukan: admin');
        }

        try {
            $validated = $request->validate(['user_id' => 'required|exists:users,id']);
            
            $user = User::find($validated['user_id']);
            
            if (!$user) {
                return back()->withErrors(['error' => 'User tidak ditemukan.'])->withInput();
            }

            if ($user->role === 'proctor') {
                return back()->withErrors(['error' => 'User ini sudah menjadi proctor.'])->withInput();
            }

            $user->role = 'proctor';
            $user->save();
            
            return redirect()->route('admin.proctor.index')->with('success', 'User berhasil dijadikan proctor.');
        } catch (\Exception $e) {
            Log::error('Error in ProctorController@store: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menjadikan user sebagai proctor: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Menampilkan halaman detail seorang proctor.
     */
    public function show(User $proctor)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES KE HALAMAN INI. Role Anda: ' . (Auth::user() ? Auth::user()->role : 'Not authenticated') . '. Role yang diperlukan: admin');
        }

        try {
            if ($proctor->role !== 'proctor') {
                abort(404, 'User ini bukan proctor.');
            }

            $groups = $proctor->trainingGroupsAsProctor()->with(['questionnaires.answers.user', 'program'])->get();

            $dataByProgram = [];
            $suggestions = [];

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

                $textQuestions = $group->questionnaires->where('type', 'text');
                foreach($textQuestions as $question) {
                    foreach($question->answers as $answer) {
                        if (!empty($answer->value)) {
                            $suggestions[$programName][] = $answer->value;
                        }
                    }
                }
            }

            $achievementByProgram = [];
            foreach ($dataByProgram as $program => $percentages) {
                $achievementByProgram[$program] = round(collect($percentages)->average());
            }

            return view('admin.proctor.show', compact('proctor', 'achievementByProgram', 'suggestions'));
        } catch (\Exception $e) {
            Log::error('Error in ProctorController@show: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data proctor.');
        }
    }

    /**
     * Menampilkan form untuk mengedit bio data proctor.
     */
    public function edit(User $proctor)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES KE HALAMAN INI. Role Anda: ' . (Auth::user() ? Auth::user()->role : 'Not authenticated') . '. Role yang diperlukan: admin');
        }

        return view('admin.proctor.edit', compact('proctor'));
    }

    /**
     * Memperbarui bio data proctor.
     */
    public function update(Request $request, User $proctor)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES KE HALAMAN INI. Role Anda: ' . (Auth::user() ? Auth::user()->role : 'Not authenticated') . '. Role yang diperlukan: admin');
        }

        try {
            if ($proctor->role !== 'proctor') {
                abort(404, 'User ini bukan proctor.');
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $proctor->id,
                'avatar' => 'nullable|string|max:255',
            ]);
            
            $proctor->update($validated);
            
            return redirect()->route('admin.proctor.show', $proctor->id)->with('success', 'Data proctor berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error in ProctorController@update: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal memperbarui data proctor: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Menghapus seorang proctor (mengubah rolenya kembali menjadi user).
     */
    public function destroy(User $proctor)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES KE HALAMAN INI. Role Anda: ' . (Auth::user() ? Auth::user()->role : 'Not authenticated') . '. Role yang diperlukan: admin');
        }

        try {
            if ($proctor->role !== 'proctor') {
                abort(404, 'User ini bukan proctor.');
            }

            $proctor->role = 'user';
            $proctor->save();
            
            return redirect()->route('admin.proctor.index')->with('success', 'Proctor berhasil dihapus (role diubah menjadi user).');
        } catch (\Exception $e) {
            Log::error('Error in ProctorController@destroy: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menghapus proctor: ' . $e->getMessage()]);
        }
    }
}
