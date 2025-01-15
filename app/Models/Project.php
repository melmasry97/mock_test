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
        'category_id',
        'evaluation_end_time',
        'module_evaluation_end_time'
    ];

    protected $casts = [
        'weight' => 'float',
        'evaluation_end_time' => 'datetime',
        'module_evaluation_end_time' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        // Listen for the types being synced
        static::$dispatcher->listen('eloquent.attached: *', function ($event, $models) {
            if (str_contains($event, 'App\Models\Project.types')) {
                [$model, $relationIds] = $models;

                foreach ($relationIds as $typeId) {
                    $type = Type::find($typeId);
                    if ($type) {
                        $defaultWeight = 1 / max(1, $type->categories->count());
                        foreach ($type->categories as $category) {
                            $model->typeCategories()->attach($category->id, [
                                'type_id' => $typeId,
                                'weight' => $defaultWeight
                            ]);
                        }
                    }
                }
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function projectModules(): HasMany
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
            ->withTimestamps()
            ->withPivot(['created_at', 'updated_at'])
            ->using(ProjectType::class);
    }

    public function typeCategories(): BelongsToMany
    {
        return $this->belongsToMany(TypeCategory::class, 'project_type_category', 'project_id', 'category_id')
            ->withPivot(['weight', 'type_id'])
            ->withTimestamps();
    }
}
