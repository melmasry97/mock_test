<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectType extends Pivot
{
    protected static function boot()
    {
        parent::boot();

        static::created(function ($pivot) {
            $project = Project::find($pivot->project_id);
            $type = Type::find($pivot->type_id);

            if ($project && $type) {
                $defaultWeight = 1 / max(1, $type->categories->count());

                foreach ($type->categories as $category) {
                    $project->typeCategories()->attach($category->id, [
                        'type_id' => $type->id,
                        'weight' => $defaultWeight
                    ]);
                }
            }
        });

        static::deleted(function ($pivot) {
            $project = Project::find($pivot->project_id);
            if ($project) {
                $project->typeCategories()
                    ->wherePivot('type_id', $pivot->type_id)
                    ->detach();
            }
        });
    }
}
