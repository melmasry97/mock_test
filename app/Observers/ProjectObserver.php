<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Type;

class ProjectObserver
{
    public function updated(Project $project)
    {
        $changes = $project->getChanges();

        // Get the newly attached types
        $types = $project->types;

        foreach ($types as $type) {
            // Check if categories for this type are already attached
            $existingCategories = $project->typeCategories()
                ->wherePivot('type_id', $type->id)
                ->count();

            if ($existingCategories === 0) {
                $defaultWeight = 1 / max(1, $type->categories->count());

                foreach ($type->categories as $category) {
                    $project->typeCategories()->attach($category->id, [
                        'type_id' => $type->id,
                        'weight' => $defaultWeight
                    ]);
                }
            }
        }
    }
}
