<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProjectModule extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'weight', 'project_id', 'category_id' , 'end_date'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function evaluations(): MorphMany
    {
        return $this->morphMany(Evaluate::class, 'evaluationable');
    }
}
