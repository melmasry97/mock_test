<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Type extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(TypeCategory::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
