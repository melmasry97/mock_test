<?php

namespace App\Models;

use App\Enums\TaskState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IsoTask extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getStateDescription()
    {
        return TaskState::fromValue($this->state)->label();
    }
}
