<?php

namespace App\Providers;

use App\Events\RiceEvaluationEnded;
use App\Events\UserEvaluationEnded;
use App\Events\ModuleEvaluationEnded;
use App\Events\CategoryEvaluationEnded;
use App\Listeners\CalculateFinalRiceScore;
use App\Listeners\CalculateFinalModuleScore;
use App\Listeners\CalculateFinalCategoryScore;
use App\Models\Task;
use App\Observers\TaskObserver;
use App\Models\Project;
use App\Observers\ProjectObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RiceEvaluationEnded::class => [
            CalculateFinalRiceScore::class,
        ],
        ModuleEvaluationEnded::class => [
            CalculateFinalModuleScore::class,
        ],
        CategoryEvaluationEnded::class => [
            CalculateFinalCategoryScore::class,
        ],
        UserEvaluationEnded::class => [
            // Add listener if needed for user evaluation
        ],
    ];

    public function boot(): void
    {
        Task::observe(TaskObserver::class);
        Project::observe(ProjectObserver::class);
    }
}
