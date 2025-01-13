<?php

namespace App\Models;

use App\Enums\TaskState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'name',
        'description',
        'weight',
        'iso_weight',
        'project_id',
        'project_module_id',
        'state',
        'rice_score',
        'overall_evaluation_value',
        'source_group_id',
        'source_id',
        'type_id',
        'status',
        'evaluation_end_time',
        'end_date',
        'average_evaluation',
        'evaluation_count',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'iso_weight' => 'float',
        'rice_score' => 'decimal:2',
        'overall_evaluation_value' => 'decimal:2',
        'evaluation_end_time' => 'datetime',
        'state' => TaskState::class,
        'end_date' => 'date',
        'average_evaluation' => 'float',
        'evaluation_count' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function projectModule(): BelongsTo
    {
        return $this->belongsTo(ProjectModule::class);
    }

    public function sourceGroup(): BelongsTo
    {
        return $this->belongsTo(SourceGroup::class, 'source_group_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'source_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(TypeCategory::class, 'task_type_category', 'task_id', 'category_id')
            ->withTimestamps();
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(TaskEvaluation::class, 'task_id');
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(Metric::class, 'task_id');
    }

    public function calculateOverallEvaluation(): void
    {
        // Step 1: Get module weight
        $moduleWeight = $this->projectModule->weight ?? 0;

        // Step 2: Get average of user Fibonacci weights
        $userFibonacciAverage = $this->evaluations->avg('fibonacci_weight') ?? 0;

        // Step 3: Calculate mean of selected type categories' weights
        $typeCategoriesAverage = $this->categories->avg('evaluation_average_value') ?? 0;

        // Step 4: Get RICE score set by admin
        $riceScore = $this->rice_score ?? 0;

        // Step 5: Calculate final overall evaluation value
        $this->overall_evaluation_value = $moduleWeight * $userFibonacciAverage * $typeCategoriesAverage * $riceScore;
        $this->save();

        // Update evaluation count
        $this->evaluation_count = $this->evaluations()->count();
        $this->average_evaluation = $userFibonacciAverage;
        $this->save();
    }
}
