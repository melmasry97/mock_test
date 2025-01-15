<?php

namespace App\Listeners;

use App\Events\CategoryEvaluationEnded;

class CalculateFinalCategoryScore
{
    public function handle(CategoryEvaluationEnded $event): void
    {
        $project = $event->project;
        $project->calculateCategoryEvaluations();
    }
}
