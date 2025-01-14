<?php

namespace App\Listeners;

use App\Events\UserEvaluationEnded;
use Illuminate\Contracts\Queue\ShouldQueue;

class CalculateFinalUserEvaluation implements ShouldQueue
{
    public function handle(UserEvaluationEnded $event): void
    {
        $event->task->calculateOverallEvaluation();
    }
}
