<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function modules()
    {
        return $this->hasMany(ProjectModule::class);
    }

    public function isoTasks(): HasMany
    {
        return $this->hasMany(IsoTask::class);
    }
}
