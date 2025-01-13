<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TypeCategory extends Model
{
    protected $table = 'type_categories';

    protected $fillable = [
        'name',
        'description',
        'time_period',
        'value',
        'average_value',
        'type_id',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'average_value' => 'decimal:2',
    ];

    protected $appends = ['remaining_time'];

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_type_category', 'category_id', 'task_id')
            ->withTimestamps();
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(TypeCategoryEvaluation::class, 'type_category_id');
    }

    public function canBeEvaluated(): bool
    {
        // Check if the evaluation period has ended
        $createdAt = Carbon::parse($this->created_at);
        $evaluationEndTime = $createdAt->addDays($this->time_period);

        if (Carbon::now()->isAfter($evaluationEndTime)) {
            return false;
        }

        // Check if the current user has already evaluated
        if (Auth::check()) {
            return !$this->evaluations()
                ->where('user_id', Auth::id())
                ->exists();
        }

        return false;
    }

    public function getEvaluationEndTime(): Carbon
    {
        return Carbon::parse($this->created_at)->addDays($this->time_period);
    }

    public function getRemainingTimeAttribute(): string
    {
        $endTime = $this->getEvaluationEndTime();
        if (Carbon::now()->isAfter($endTime)) {
            return 'Evaluation period ended';
        }
        return Carbon::now()->diffForHumans($endTime, ['parts' => 2]);
    }

    public function calculateAverageEvaluation(): void
    {
        $this->average_value = $this->evaluations()->avg('fibonacci_weight') ?? 0;
        $this->save();
    }
}
