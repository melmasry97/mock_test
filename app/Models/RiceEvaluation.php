<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiceEvaluation extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'reach',
        'impact',
        'confidence',
        'effort',
        'score',
    ];

    protected $casts = [
        'reach' => 'integer',
        'impact' => 'integer',
        'confidence' => 'integer',
        'effort' => 'integer',
        'score' => 'float',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($evaluation) {
            // Calculate RICE score
            $evaluation->score = ($evaluation->reach * $evaluation->impact * $evaluation->confidence) / $evaluation->effort;
        });
    }
}
