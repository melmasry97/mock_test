<?php

namespace App\Console\Commands;

use App\Events\UserEvaluationEnded;
use App\Models\Task;
use Illuminate\Console\Command;

class CheckUserEvaluationDeadlines extends Command
{
    protected $signature = 'app:check-user-deadlines';

    protected $description = 'Check for tasks that have reached their user evaluation deadline';

    public function handle(): void
    {
        $tasks = Task::query()
            ->where('status', 'approved')
            ->whereNotNull('evaluation_end_time')
            ->where('evaluation_end_time', '<=', now())
            ->get();

        foreach ($tasks as $task) {
            UserEvaluationEnded::dispatch($task);
            $this->info("Dispatched UserEvaluationEnded event for task: {$task->id}");
        }
    }
}
