<?php

namespace App\Observers;

use App\Events\RiceEvaluationEnded;
use App\Events\UserEvaluationEnded;
use App\Models\Task;

class TaskObserver
{
    public function updated(Task $record): void
    {
        // Check for RICE evaluation end time
        if ($record->rice_evaluation_end_time?->isPast() && $record->status === 'pending') {
            RiceEvaluationEnded::dispatch($record);
        }

        // Check for user evaluation end time
        if ($record->evaluation_end_time?->isPast() && $record->status === 'approved') {
            UserEvaluationEnded::dispatch($record);
        }
    }
}
