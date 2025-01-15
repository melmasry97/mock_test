<?php

namespace App\Listeners;

use App\Events\UserEvaluationEnded;
use Illuminate\Contracts\Queue\ShouldQueue;

class CalculateFinalUserEvaluation implements ShouldQueue
{
    public function handle(UserEvaluationEnded $event): void
    {
        // Calculate overall evaluation (fibonacci weight average)
        $event->task->calculateOverallEvaluation();

        // Calculate final weight using all components
        $event->task->calculateFinalWeight();
    }
}
