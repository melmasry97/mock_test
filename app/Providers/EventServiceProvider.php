<?php

namespace App\Providers;

use App\Events\RiceEvaluationEnded;
use App\Listeners\CalculateFinalRiceScore;
use App\Models\Task;
use App\Observers\TaskObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RiceEvaluationEnded::class => [
            CalculateFinalRiceScore::class,
        ],
    ];

    public function boot(): void
    {
        Task::observe(TaskObserver::class);
    }
}
