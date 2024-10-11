<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IsoTask extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'weight', 'project_id', 'end_date'];

    protected $casts = [
        'end_date' => 'date',
        'weight' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function evaluations(): MorphMany
    {
        return $this->morphMany(Evaluate::class, 'evaluationable');
    }
}
