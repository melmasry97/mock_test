<?php

namespace Database\Factories;

use App\Models\IsoTask;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class IsoTaskFactory extends Factory
{
    protected $model = IsoTask::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),
            'weight' => $this->faker->randomFloat(2, 1, 100),
            'category_id' => Category::factory(),
            'description' => $this->faker->paragraph(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
