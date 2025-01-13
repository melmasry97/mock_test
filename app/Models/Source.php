<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    protected $fillable = [
        'name',
        'description',
        'source_group_id',
    ];

    public function sourceGroup(): BelongsTo
    {
        return $this->belongsTo(SourceGroup::class, 'source_group_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
