<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Metric extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'matrix_values' => 'array',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
