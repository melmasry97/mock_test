<?php

namespace App\Listeners;

use App\Events\ModuleEvaluationEnded;

class CalculateFinalModuleScore
{
    public function handle(ModuleEvaluationEnded $event): void
    {
        $project = $event->project;
        $project->calculateModuleEvaluations();
    }
}
