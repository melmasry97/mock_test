<?php

namespace Database\Seeders;

use App\Models\ProjectModule;
use App\Models\Project;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProjectModuleSeeder extends Seeder
{
    public function run()
    {
        $projects = Project::all();
        $categories = Category::all();

        foreach ($projects as $project) {
            foreach ($categories as $category) {
                ProjectModule::create([
                    'name' => fake()->words(3, true),
                    'weight' => fake()->numberBetween(1, 5),
                    'project_id' => $project->id,
                    'category_id' => $category->id,
                ]);
            }
        }
    }
}
