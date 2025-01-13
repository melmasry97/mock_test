<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    protected $primaryKey = 'source_id';

    protected $fillable = [
        'source_name',
        'source_description',
        'group_id',
    ];

    public function sourceGroup(): BelongsTo
    {
        return $this->belongsTo(SourceGroup::class, 'group_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'source_id');
    }
}
