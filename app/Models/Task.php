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
        'weight',
        // Add any other fields that should be mass-assignable
    ];

    protected $casts = [
        'state' => TaskState::class,
    ];

    protected $guarded = [];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function isoTask()
    {
        return $this->belongsTo(IsoTask::class);
    }

    public function getStateDescription()
    {
        return TaskState::fromValue($this->state)->label();
    }
}
