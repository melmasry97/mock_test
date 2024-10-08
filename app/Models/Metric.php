<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Metric extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'module_weight',
        'input1',
        'input2',
        'input3',
        'input4',
        'calculated_value',
        'matrix_values',
    ];

    protected $casts = [
        'matrix_values' => 'array',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
