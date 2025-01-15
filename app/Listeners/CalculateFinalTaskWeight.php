<?php

namespace App\Listeners;

use App\Events\UserEvaluationEnded;
use Illuminate\Support\Facades\DB;

class CalculateFinalTaskWeight
{
    public function handle(UserEvaluationEnded $event): void
    {
        $task = $event->task;
        $project = $task->project;

        // Get RICE score (average of all RICE evaluations)
        $riceScore = $task->rice_score ?? 0;

        // Get module average evaluations
        $moduleScore = $task->projectModule?->average_value ?? 0;

        // Get average of project type categories scores
        $categoriesScore = $project->typeCategories()
            ->whereNotNull('average_value')
            ->avg('average_value') ?? 0;

        // Get user evaluations average
        $userEvaluationScore = $task->overall_evaluation_value ?? 0;

        // Calculate final weight
        $finalWeight = $riceScore + $moduleScore + $categoriesScore + $userEvaluationScore;

        // Update task weight
        $task->update([
            'weight' => $finalWeight
        ]);

        // Log the calculation components for debugging
        \Log::info('Task weight calculation', [
            'task_id' => $task->id,
            'rice_score' => $riceScore,
            'module_score' => $moduleScore,
            'categories_score' => $categoriesScore,
            'user_evaluation_score' => $userEvaluationScore,
            'final_weight' => $finalWeight
        ]);
    }
}
