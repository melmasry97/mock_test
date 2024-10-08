<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),
            'weight' => $this->faker->randomFloat(2, 0, 100),
            'category_id' => Category::factory(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
