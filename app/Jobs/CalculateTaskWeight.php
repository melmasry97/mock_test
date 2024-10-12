<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\Metric;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateTaskWeight implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        Task::where('end_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('weight')
                    ->orWhere('weight_calculated_at', '<', now()->subDay());
            })
            ->chunk(100, function ($tasks) {
                foreach ($tasks as $task) {
                    $moduleWeight = $task->projectModule->weight ?? 1;

                    $metrics = Metric::where('task_id', $task->id)->get();
                    $metricsAverage = $metrics->avg('calculated_values');
                    $matrixCalculatedValue = $metrics->first()->matrix_calculated_value ?? 1;

                    $taskWeight = $moduleWeight * $metricsAverage * $matrixCalculatedValue;

                    $task->update([
                        'weight' => $taskWeight,
                        'weight_calculated_at' => now(),
                    ]);
                }
            });
    }
}
