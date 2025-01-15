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
        'project_id',
        'project_module_id',
        'source_group_id',
        'source_id',
        'status',
        'rice_evaluation_end_time',
        'evaluation_end_time',
        'reach',
        'impact',
        'confidence',
        'effort',
        'rice_score',
        'overall_evaluation_value',
        'weight'
    ];

    protected $casts = [
        'rice_evaluation_end_time' => 'datetime',
        'evaluation_end_time' => 'datetime',
        'reach' => 'float',
        'impact' => 'float',
        'confidence' => 'float',
        'effort' => 'float',
        'rice_score' => 'float',
        'overall_evaluation_value' => 'float',
        'weight' => 'float'
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

    public function riceEvaluations(): HasMany
    {
        return $this->hasMany(RiceEvaluation::class);
    }

    public function getUserEvaluationRemainingTimeAttribute(): ?string
    {
        if (!$this->evaluation_end_time) {
            return null;
        }

        if ($this->evaluation_end_time->isPast()) {
            return 'Ended';
        }

        return now()->diffForHumans($this->evaluation_end_time, ['parts' => 2]);
    }

    public function getRiceEvaluationRemainingTimeAttribute(): ?string
    {
        if (!$this->rice_evaluation_end_time) {
            return null;
        }

        if ($this->rice_evaluation_end_time->isPast()) {
            return 'Ended';
        }

        return now()->diffForHumans($this->rice_evaluation_end_time, ['parts' => 2]);
    }

    public function canBeEvaluatedByAdmin(): bool
    {
        return $this->rice_evaluation_end_time?->isFuture() ?? false;
    }

    public function canBeEvaluatedByUser(): bool
    {
        return $this->status === 'approved' &&
               $this->evaluation_end_time?->isFuture() ?? false;
    }

    public function calculateFinalRiceScore(): void
    {
        if ($this->rice_evaluation_end_time->isPast()) {
            $evaluations = $this->riceEvaluations;

            if ($evaluations->count() > 0) {
                $this->reach = (int) round($evaluations->avg('reach'));
                $this->impact = (int) round($evaluations->avg('impact'));
                $this->confidence = (int) round($evaluations->avg('confidence'));
                $this->effort = (int) round($evaluations->avg('effort'));
                $this->rice_score = ($this->reach * $this->impact * $this->confidence) / $this->effort;
                $this->status = 'evaluating';
                $this->save();
            }
        }
    }

    public function calculateOverallEvaluation(): void
    {
        $evaluations = $this->evaluations;

        if ($evaluations->count() > 0) {
            $this->overall_evaluation_value = $evaluations->avg('fibonacci_weight');
            $this->save();
        }
    }
}
