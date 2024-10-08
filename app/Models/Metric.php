<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Metric extends Model
{
    use HasFactory;

    protected $fillable = [
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
}
