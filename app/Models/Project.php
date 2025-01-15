<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'weight',
        'category_id'
    ];

    protected $casts = [
        'weight' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        // When types are attached to a project
        static::$dispatcher->listen('eloquent.attached: ' . static::class . '.types', function ($project, $ids) {
            foreach ($ids as $typeId) {
                $type = Type::find($typeId);
                foreach ($type->categories as $category) {
                    $project->typeCategories()->attach($category->id, [
                        'type_id' => $typeId,
                        'weight' => 0
                    ]);
                }
            }
        });

        // When types are detached from a project
        static::$dispatcher->listen('eloquent.detached: ' . static::class . '.types', function ($project, $ids) {
            foreach ($ids as $typeId) {
                $project->typeCategories()
                    ->wherePivot('type_id', $typeId)
                    ->detach();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(ProjectModule::class);
    }

    public function isoTasks(): HasMany
    {
        return $this->hasMany(IsoTask::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function types(): BelongsToMany
    {
        return $this->belongsToMany(Type::class, 'project_type', 'project_id', 'type_id')
            ->withTimestamps();
    }

    public function typeCategories(): BelongsToMany
    {
        return $this->belongsToMany(TypeCategory::class, 'project_type_category', 'project_id', 'category_id')
            ->withPivot(['weight', 'type_id'])
            ->withTimestamps();
    }
}
