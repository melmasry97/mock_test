<?php

namespace App\Listeners;

use App\Events\RiceEvaluationEnded;
use Illuminate\Contracts\Queue\ShouldQueue;

class CalculateFinalRiceScore implements ShouldQueue
{
    public function handle(RiceEvaluationEnded $event): void
    {
        $task = $event->task;
        $task->calculateFinalRiceScore();
    }
}
