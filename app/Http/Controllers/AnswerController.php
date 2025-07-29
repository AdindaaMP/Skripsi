<?php

namespace App\Http\Controllers;

use App\Models\TrainingGroup;
use App\Models\Answer;
use App\Models\Questionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AnswerController extends Controller
{
    /**
     * Menampilkan form evaluasi kepada user.
     */
    public function form(TrainingGroup $group)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return redirect()->route('login');
            }

            // Load questionnaires untuk group ini
            $group->load(['questionnaires', 'activity', 'program']);

            // Pastikan program dimuat dengan benar
            if (!$group->program && $group->program_id) {
                $group->program = \App\Models\Program::find($group->program_id);
            }

            // Pastikan group ada questionnaires
            if ($group->questionnaires->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada kuesioner untuk kelompok ini.');
            }

            $questionsQuery = $group->questionnaires()->newQuery(); 

            // Filter pertanyaan berdasarkan role
            if ($user->role === 'trainer') {
                // Trainer tidak akan melihat pertanyaan bertipe text
                $questionsQuery->where('type', '!=', 'text');
            }
            // Proctor dan user biasa bisa melihat semua pertanyaan
            
            $questions = $questionsQuery->orderBy('order', 'asc')->get();

            $userAnswers = $user->answers()
                ->whereIn('questionnaire_id', $questions->pluck('id'))
                ->where('training_group_id', $group->id)
                ->get()
                ->keyBy('questionnaire_id');

            $hasAnsweredAll = $questions->count() > 0 && $questions->count() === $userAnswers->count();
            
            return view('evaluasi.form', compact('group', 'questions', 'hasAnsweredAll', 'userAnswers'));
        } catch (\Exception $e) {
            Log::error('Error in AnswerController@form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat form evaluasi.');
        }
    }

    /**
     * Menyimpan atau memperbarui jawaban evaluasi dari user.
     */
    public function submit(Request $request, TrainingGroup $group)
    {
        try {
            $request->validate([
                'answers' => 'required|array',
                'answers.*.value' => 'nullable', 
            ]);

            $user = Auth::user();

            foreach ($request->answers as $questionId => $answerData) {
                Answer::updateOrCreate(
                    [
                        'questionnaire_id'    => $questionId,
                        'user_id'             => $user->id,
                        'training_group_id'   => $group->id, // penting!
                    ],
                    [
                        'value' => $answerData['value'],
                    ]
                );
            }

            // Redirect berdasarkan role user
            if ($user->role === 'trainer') {
                return redirect()
                    ->route('home.user')
                    ->with('success', 'Terima kasih, evaluasi Anda telah berhasil disimpan!');
            } else {
                return redirect()
                    ->route('home.user')
                    ->with('success', 'Terima kasih, evaluasi Anda telah berhasil disimpan!');
            }
        } catch (\Exception $e) {
            Log::error('Error in AnswerController@submit: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan evaluasi. Silakan coba lagi.');
        }
    }
}
