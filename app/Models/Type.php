<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Type extends Model
{
    protected $primaryKey = 'type_id';

    protected $fillable = [
        'type_name',
        'type_description',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(TypeCategory::class, 'type_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'type_id');
    }
}
