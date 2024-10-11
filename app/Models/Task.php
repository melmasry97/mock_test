<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TaskState;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'state',
        'weight', // This can now be null
        'project_module_id',
        'end_date',
    ];

    protected $casts = [
        'state' => TaskState::class,
        'weight' => 'float',
    ];

    public function metrics()
    {
        return $this->hasMany(Metric::class);
    }

    public function projectModule()
    {
        return $this->belongsTo(ProjectModule::class);
    }
}
