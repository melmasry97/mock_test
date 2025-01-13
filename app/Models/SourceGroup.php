<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SourceGroup extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    public function sources(): HasMany
    {
        return $this->hasMany(Source::class, 'source_group_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'source_group_id');
    }
}
