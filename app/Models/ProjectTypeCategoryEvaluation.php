<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTypeCategoryEvaluation extends Model
{
    protected $fillable = [
        'project_id',
        'type_id',
        'category_id',
        'user_id',
        'weight',
        'evaluation_end_time'
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'evaluation_end_time' => 'datetime'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TypeCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
