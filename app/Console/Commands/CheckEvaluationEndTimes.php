<?php

namespace App\Console\Commands;

use App\Events\CategoryEvaluationEnded;
use App\Events\ModuleEvaluationEnded;
use App\Events\RiceEvaluationEnded;
use App\Events\UserEvaluationEnded;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckEvaluationEndTimes extends Command
{
    protected $signature = 'evaluation:check-end-times';
    protected $description = 'Check and process ended evaluations';

    public function handle(): void
    {
        // Check Project Category Evaluations
        Project::query()
            ->whereNotNull('evaluation_end_time')
            ->where('evaluation_end_time', '<=', now())
            ->whereDoesntHave('typeCategories', function ($query) {
                $query->whereNotNull('average_value');
            })
            ->each(function (Project $project) {
                CategoryEvaluationEnded::dispatch($project);
                $this->info("Category evaluation ended for project: {$project->name}");
            });

        // Check Project Module Evaluations
        Project::query()
            ->whereNotNull('module_evaluation_end_time')
            ->where('module_evaluation_end_time', '<=', now())
            ->whereDoesntHave('projectModules', function ($query) {
                $query->whereNotNull('average_value');
            })
            ->each(function (Project $project) {
                ModuleEvaluationEnded::dispatch($project);
                $this->info("Module evaluation ended for project: {$project->name}");
            });

        // Check Task RICE Evaluations
        Task::query()
            ->whereNotNull('rice_evaluation_end_time')
            ->where('rice_evaluation_end_time', '<=', now())
            ->whereNull('rice_score')
            ->each(function (Task $task) {
                RiceEvaluationEnded::dispatch($task);
                $this->info("RICE evaluation ended for task: {$task->name}");
            });

        // Check Task User Evaluations
        Task::query()
            ->whereNotNull('evaluation_end_time')
            ->where('evaluation_end_time', '<=', now())
            ->whereNull('overall_evaluation_value')
            ->each(function (Task $task) {
                UserEvaluationEnded::dispatch($task);
                $this->info("User evaluation ended for task: {$task->name}");
            });
    }
}
