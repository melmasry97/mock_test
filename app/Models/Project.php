<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'weight',
        'category_id'
    ];

    protected $casts = [
        'weight' => 'float',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(ProjectModule::class);
    }

    public function isoTasks(): HasMany
    {
        return $this->hasMany(IsoTask::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function types(): HasMany
    {
        return $this->hasMany(Type::class);
    }
}
