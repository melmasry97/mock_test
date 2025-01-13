<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SourceGroup extends Model
{
    protected $primaryKey = 'group_id';

    protected $fillable = [
        'group_name',
        'group_description',
    ];

    public function sources(): HasMany
    {
        return $this->hasMany(Source::class, 'group_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'source_group_id');
    }
}
