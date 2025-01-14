<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Type extends Model
{
    protected $fillable = [
        'name',
        'description',
        'project_id'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(TypeCategory::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
