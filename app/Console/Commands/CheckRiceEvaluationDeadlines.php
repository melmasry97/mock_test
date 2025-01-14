<?php

namespace App\Console\Commands;

use App\Events\RiceEvaluationEnded;
use App\Models\Task;
use Illuminate\Console\Command;

class CheckRiceEvaluationDeadlines extends Command
{
    protected $signature = 'app:check-rice-deadlines';
    protected $description = 'Check for tasks with expired RICE evaluation deadlines and calculate final scores';

    public function handle(): void
    {
        $tasks = Task::query()
            ->where('status', 'pending')
            ->whereNotNull('rice_evaluation_end_time')
            ->where('rice_evaluation_end_time', '<=', now())
            ->get();

        foreach ($tasks as $task) {
            event(new RiceEvaluationEnded($task));
            $this->info("Processing RICE evaluation for task: {$task->name}");
        }
    }
}
