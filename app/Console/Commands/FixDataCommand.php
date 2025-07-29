<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TrainingGroup;
use App\Models\Program;
use App\Models\Questionnaire;
use App\Models\Answer;
use Illuminate\Support\Facades\Log;

class FixDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:fix-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix all data synchronization issues including programs, questionnaires, and answers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai perbaikan data...');

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
            $this->info("Total training groups: {$results['total_groups']}");

            $progressBar = $this->output->createProgressBar($trainingGroups->count());
            $progressBar->start();

            foreach ($trainingGroups as $group) {
                try {
                    $this->line("\nProcessing group: {$group->name}");
                    
                    // 1. Perbaiki program yang tidak ada
                    if (!$group->program_id) {
                        $firstProgram = Program::first();
                        if ($firstProgram) {
                            $group->update(['program_id' => $firstProgram->id]);
                            $results['programs_fixed']++;
                            $this->info("  - Fixed missing program");
                        }
                    } elseif (!$group->program) {
                        $program = Program::find($group->program_id);
                        if (!$program) {
                            $firstProgram = Program::first();
                            if ($firstProgram) {
                                $group->update(['program_id' => $firstProgram->id]);
                                $results['programs_fixed']++;
                                $this->info("  - Fixed invalid program");
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
                                $this->info("  - Fixed missing questionnaires");
                            }
                        } catch (\Exception $e) {
                            $results['errors'][] = "Error cloning questionnaires for group {$group->id}: " . $e->getMessage();
                            $this->error("  - Error cloning questionnaires: " . $e->getMessage());
                        }
                    }

                    // 3. Perbaiki answers yang tidak valid
                    $questionnaires = Questionnaire::where('training_group_id', $group->id)->get();
                    $invalidAnswers = Answer::where('training_group_id', $group->id)
                        ->whereNotIn('questionnaire_id', $questionnaires->pluck('id'))
                        ->get();
                    
                    if ($invalidAnswers->count() > 0) {
                        $invalidAnswers->delete();
                        $results['answers_fixed']++;
                        $this->info("  - Fixed invalid answers");
                    }

                    $results['groups_fixed']++;
                    $progressBar->advance();
                } catch (\Exception $e) {
                    $results['errors'][] = "Error processing group {$group->id}: " . $e->getMessage();
                    $this->error("  - Error processing group: " . $e->getMessage());
                }
            }

            $progressBar->finish();

            $this->newLine();
            $this->info("\n=== HASIL PERBAIKAN ===");
            $this->info("Total groups: {$results['total_groups']}");
            $this->info("Groups fixed: {$results['groups_fixed']}");
            $this->info("Programs fixed: {$results['programs_fixed']}");
            $this->info("Questionnaires fixed: {$results['questionnaires_fixed']}");
            $this->info("Answers fixed: {$results['answers_fixed']}");
            
            if (!empty($results['errors'])) {
                $this->error("\nErrors:");
                foreach ($results['errors'] as $error) {
                    $this->error("- {$error}");
                }
            }

            $this->info("\nPerbaikan data selesai!");

        } catch (\Exception $e) {
            $this->error("General error: " . $e->getMessage());
        }
    }
} 