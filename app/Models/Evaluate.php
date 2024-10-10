<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluate extends Model
{
    protected $table = 'evaluationables';

    protected $fillable = ['weight', 'evaluationable_id', 'evaluationable_type', 'user_id'];

    public function evaluationable()
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projectModules(): MorphToMany
    {
        return $this->morphedByMany(ProjectModule::class, 'evaluationable');
    }

    public function isoTasks(): MorphToMany
    {
        return $this->morphedByMany(IsoTask::class, 'evaluationable');
    }

    public function tasks(): MorphToMany
    {
        return $this->morphedByMany(Task::class, 'evaluationable');
    }
}
