<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Questionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProgramQuestionnaireController extends Controller
{
    public function index(Program $program)
    {
        $questions = $program->questionnaires()
            ->whereNull('training_group_id')
            ->orderBy('order')
            ->get();

        return view('admin.program_questionnaires.index', [
            'program' => $program,
            'questions' => $questions,
        ]);
    }

    public function store(Request $request, Program $program)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:yes_no,text',
        ]);

        $maxOrder = $program->questionnaires()
            ->whereNull('training_group_id')
            ->max('order') ?? 0;

        $questionnaire = Questionnaire::create([
            'question' => $validated['question'],
            'type' => $validated['type'],
            'program_id' => $program->id,
            'order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Pertanyaan berhasil ditambahkan ke program.');
    }

    public function update(Request $request, Program $program, Questionnaire $questionnaire)
    {
        $validated = $request->validate([
            'question' => 'required|string',
        ]);

        $questionnaire->update($validated);
        return back()->with('success', 'Pertanyaan berhasil diperbarui.');
    }

    public function destroy(Program $program, Questionnaire $questionnaire)
    {
        $questionnaire->delete();
        return back()->with('success', 'Pertanyaan berhasil dihapus.');
    }

    public function updateOrder(Request $request, Program $program)
    {
        try {
            $request->validate(['order' => 'required|array']);

            foreach ($request->order as $index => $questionId) {
                Questionnaire::where('id', $questionId)
                    ->where('program_id', $program->id)
                    ->whereNull('training_group_id')
                    ->update(['order' => $index]);
            }

            return response()->json(['status' => 'success', 'message' => 'Urutan berhasil disimpan.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan urutan: ' . $e->getMessage()]);
        }
    }

    public function syncToGroups(Program $program)
    {
        try {
            // Ambil semua kelompok yang menggunakan program ini
            $groups = \App\Models\TrainingGroup::where('program_id', $program->id)->get();

            if ($groups->isEmpty()) {
                return back()->with('warning', 'Tidak ada kelompok yang menggunakan program ini.');
            }

            $successCount = 0;
            $errorCount = 0;

            foreach ($groups as $group) {
                try {
                    // Load program untuk memastikan relasi tersedia
                    $group->load('program');
                    
                    // Pastikan program dimuat dengan benar
                    if (!$group->program && $group->program_id) {
                        $group->program = \App\Models\Program::find($group->program_id);
                    }
                    
                    if (!$group->program) {
                        Log::error("Program tidak ditemukan untuk training group {$group->id}");
                        $errorCount++;
                        continue;
                    }
                    
                    $group->syncQuestionnairesFromProgram();
                    $successCount++;
                    
                    Log::info("Successfully synced questionnaires for training group '{$group->name}' from program '{$program->name}'");
                } catch (\Exception $e) {
                    Log::error("Error syncing questionnaires for training group {$group->id}: " . $e->getMessage());
                    $errorCount++;
                }
            }

            $message = "Berhasil sinkronisasi ke {$successCount} kelompok.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} kelompok gagal sinkronisasi.";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error("Error in syncToGroups for program {$program->id}: " . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal sinkronisasi: ' . $e->getMessage()]);
        }
    }
} 