<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use App\Models\TrainingGroup;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse; 
use Illuminate\Support\Str; 

class QuestionnaireController extends Controller
{
    public function index(TrainingGroup $group)
    {
        $group->load('program', 'activity');
        $questions = $group->questionnaires()->orderBy('order')->get();

        return view('admin.kuesioner.index', [
            'questions' => $questions,
            'program' => $group->program,
            'activity' => $group->activity,
            'group' => $group,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'question' => 'required|string',
                'training_group_id' => 'required|exists:training_groups,id',
                'type' => 'required|in:yes_no,text',
            ]);

            $group = TrainingGroup::with('program', 'activity')->findOrFail($validated['training_group_id']);

            $maxOrder = Questionnaire::where('training_group_id', $group->id)->max('order') ?? 0;
            
            $questionnaire = Questionnaire::create([
                'question' => $validated['question'],
                'type' => $validated['type'],
                'training_group_id' => $group->id,
                'program_id' => $group->program_id,
                'activity_id' => $group->activity_id,
                'order' => $maxOrder + 1,
            ]);

            return back()->with('success', 'Pertanyaan berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menambahkan pertanyaan: ' . $e->getMessage()]);
        }
    }


    public function cloneFromProgramToActivity($programId, $activityId)
    {
        $questions = Questionnaire::where('program_id', $programId)->get();

        foreach ($questions as $question) {
            Questionnaire::create([
                'question' => $question->question,
                'type' => $question->type,
                'order' => $question->order,
                'activity_id' => $activityId,
            ]);
        }
    }


    public function update(Request $request, Questionnaire $questionnaire)
    {
        $validated = $request->validate(['question' => 'required|string']);
        $questionnaire->update($validated);
        return back()->with('success', 'Pertanyaan berhasil diperbarui.');
    }

    public function destroy(Questionnaire $questionnaire)
    {
        $questionnaire->delete();
        return back()->with('success', 'Pertanyaan berhasil dihapus.');
    }

    public function updateOrder(Request $request)
    {
        try {
            $request->validate(['order' => 'required|array']);

            foreach ($request->order as $index => $questionId) {
                Questionnaire::where('id', $questionId)->update(['order' => $index]);
            }

            return response()->json(['status' => 'success', 'message' => 'Urutan berhasil disimpan.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan urutan: ' . $e->getMessage()]);
        }
    }

    public function results(Request $request, TrainingGroup $group)
    {
        $group->load('questionnaires.answers.user', 'users', 'trainers', 'proctors');
        $questions = $group->questionnaires()->orderBy('order')->get();
        $questionnaireIds = $questions->pluck('id');

        $allMembers = $group->users->merge($group->trainers)->merge($group->proctors)->unique('id');

        $members = $allMembers->filter(function ($member) use ($questionnaireIds, $group) {
            return $member->answers()
                ->whereIn('questionnaire_id', $questionnaireIds)
                ->where('training_group_id', $group->id)
                ->exists();
        });

        if ($searchTerm = $request->search) {
            $members = $members->filter(function ($member) use ($searchTerm) {
                return Str::contains(strtolower($member->name), strtolower($searchTerm)) ||
                       Str::contains(strtolower($member->email), strtolower($searchTerm));
            });
        }

        $answersByMember = [];
        foreach ($members as $member) {
            $answersByMember[$member->id] = $member->answers()
                ->whereIn('questionnaire_id', $questionnaireIds)
                ->where('training_group_id', $group->id)
                ->get()->keyBy('questionnaire_id');
        }

        return view('admin.kuesioner.results', compact('group', 'questions', 'members', 'answersByMember'));
    }

    public function exportCsv(Request $request, TrainingGroup $group)
    {
        $group->load('questionnaires.answers.user', 'users', 'trainers', 'proctors');
        $questions = $group->questionnaires()->orderBy('order')->get();
        $questionnaireIds = $questions->pluck('id');
        $allMembers = $group->users->merge($group->trainers)->merge($group->proctors)->unique('id');

        $members = $allMembers->filter(fn($m) =>
            $m->answers()
              ->whereIn('questionnaire_id', $questionnaireIds)
              ->where('training_group_id', $group->id)
              ->exists()
        );

        if ($searchTerm = $request->search) {
            $members = $members->filter(fn($m) => Str::contains(strtolower($m->name), strtolower($searchTerm)) || Str::contains(strtolower($m->email), strtolower($searchTerm)));
        }

        $filename = 'hasil-evaluasi-' . Str::slug($group->name) . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($members, $questions, $questionnaireIds, $group) {
            $file = fopen('php://output', 'w');
            $headerRow = ['Nama', 'Email', 'Role'];
            foreach ($questions as $question) {
                $headerRow[] = $question->question;
            }
            fputcsv($file, $headerRow);

            foreach ($members as $member) {
                $row = [$member->name, $member->email, ucfirst($member->role)];
                $userAnswers = $member->answers()
                    ->whereIn('questionnaire_id', $questionnaireIds)
                    ->where('training_group_id', $group->id)
                    ->get()
                    ->keyBy('questionnaire_id');
                foreach ($questions as $question) {
                    $answer = $userAnswers[$question->id] ?? null;
                    if ($answer) {
                        $row[] = ($question->type == 'yes_no') ? ($answer->value ? 'Ya' : 'Tidak') : $answer->value;
                    } else {
                        $row[] = '-';
                    }
                }
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
