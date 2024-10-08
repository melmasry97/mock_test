<?php

namespace Database\Factories;

use App\Models\ProjectModule;
use App\Models\Project;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectModuleFactory extends Factory
{
    protected $model = ProjectModule::class;

    public function definition()
    {
        $project = Project::factory()->create();
        return [
            'name' => $this->faker->words(3, true),
            'weight' => $this->faker->randomFloat(2, 1, 100),
            'project_id' => $project->id,
            'category_id' => $project->category_id,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
