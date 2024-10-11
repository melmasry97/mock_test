<?php

namespace Database\Factories;

use App\Models\IsoTask;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class IsoTaskFactory extends Factory
{
    protected $model = IsoTask::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'weight' => $this->faker->numberBetween(1, 100),
            'project_id' => Project::factory(),
            'end_date' => $this->faker->dateTimeBetween('+1 week', '+1 year')->format('Y-m-d'),
        ];
    }
}
