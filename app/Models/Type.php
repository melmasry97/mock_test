<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Type extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_type', 'type_id', 'project_id')
            ->withTimestamps();
    }

    public function categories(): HasMany
    {
        return $this->hasMany(TypeCategory::class, 'type_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'type_id');
    }
}
