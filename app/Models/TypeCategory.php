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
    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category_name',
        'category_description',
        'evaluation_time_period',
        'evaluation_value',
        'evaluation_average_value',
        'type_id',
    ];

    protected $casts = [
        'evaluation_value' => 'decimal:2',
        'evaluation_average_value' => 'decimal:2',
    ];

    protected $appends = ['remaining_time'];

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_type_category', 'category_id', 'task_id')
            ->withTimestamps();
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(TypeCategoryEvaluation::class, 'type_category_id', 'category_id');
    }

    public function canBeEvaluated(): bool
    {
        // Check if the evaluation period has ended
        $createdAt = Carbon::parse($this->created_at);
        $evaluationEndTime = $createdAt->addDays($this->evaluation_time_period);

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
        return Carbon::parse($this->created_at)->addDays($this->evaluation_time_period);
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
        $this->evaluation_average_value = $this->evaluations()->avg('fibonacci_weight') ?? 0;
        $this->save();
    }
}
