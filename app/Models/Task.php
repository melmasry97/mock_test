<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TaskState;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'state',
        // Add other existing fields
    ];

    protected $casts = [
        'state' => TaskState::class,
        'weight' => 'float',
        'iso_weight' => 'float',
    ];

    public function projectModule()
    {
        return $this->belongsTo(ProjectModule::class);
    }

    public function getStateDescription()
    {
        return $this->state->label();
    }

    public function metric()
    {
        return $this->hasOne(Metric::class);
    }
}
