<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class TrainingGroup extends Model
{
    use HasFactory;

        protected $fillable = [
        'name',
        'kuota',
        'activity_id',
        'program_id',
    ];


    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'training_group_user');
    }
    
    public function trainers()
    {
        return $this->belongsToMany(User::class, 'group_trainer');
    }

    public function proctors()
    {
        return $this->belongsToMany(User::class, 'group_proctor');
    }

    public function questionnaires()
    {
        return $this->hasMany(Questionnaire::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function programQuestionnaires()
    {
        return $this->program ? $this->program->questionnaires()->get() : collect();
    }

    public function cloneQuestionnairesFromProgram()
    {
        if (!$this->program_id) {
            Log::warning("TrainingGroup {$this->id} tidak memiliki program_id");
            return;
        }

        // Pastikan program dimuat
        if (!$this->program) {
            $this->program = Program::find($this->program_id);
        }

        // Pastikan program ada
        if (!$this->program) {
            Log::error("Program dengan ID {$this->program_id} tidak ditemukan untuk TrainingGroup {$this->id}");
            throw new \Exception("Program dengan ID {$this->program_id} tidak ditemukan.");
        }

        try {
            // Ambil semua kuesioner dari program (baik yang default maupun yang sudah dimodifikasi)
            $programQuestions = $this->program->questionnaires()
                ->whereNull('training_group_id')
                ->orderBy('order')
                ->get();

            Log::info("Cloning {$programQuestions->count()} questionnaires from program {$this->program->name} to training group {$this->name}");

            foreach ($programQuestions as $question) {
                Questionnaire::create([
                    'question' => $question->question,
                    'type' => $question->type,
                    'order' => $question->order,
                    'program_id' => $this->program_id,
                    'training_group_id' => $this->id,
                    'activity_id' => $this->activity_id,
                ]);
            }

            Log::info("Successfully cloned questionnaires for training group {$this->name}");
        } catch (\Exception $e) {
            Log::error("Error cloning questionnaires for training group {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function syncQuestionnairesFromProgram()
    {
        if (!$this->program_id) {
            Log::warning("TrainingGroup {$this->id} tidak memiliki program_id untuk sync");
            return;
        }

        try {
            // Pastikan program dimuat
            if (!$this->program) {
                $this->program = Program::find($this->program_id);
            }

            if (!$this->program) {
                Log::error("Program dengan ID {$this->program_id} tidak ditemukan untuk sync TrainingGroup {$this->id}");
                throw new \Exception("Program dengan ID {$this->program_id} tidak ditemukan.");
            }

            // Hapus kuesioner yang ada
            $deletedCount = $this->questionnaires()->delete();
            Log::info("Deleted {$deletedCount} existing questionnaires for training group {$this->name}");

            // Clone ulang dari program
            $this->cloneQuestionnairesFromProgram();
            
            Log::info("Successfully synced questionnaires for training group {$this->name}");
        } catch (\Exception $e) {
            Log::error("Error syncing questionnaires for group {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

}
