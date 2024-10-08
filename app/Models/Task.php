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
        'weight',
        'project_module_id',
    ];

    protected $casts = [
        'state' => TaskState::class,
    ];

    public function metric()
    {
        return $this->hasOne(Metric::class);
    }

    public function projectModule()
    {
        return $this->belongsTo(ProjectModule::class);
    }
}
