<?php

namespace Database\Factories;

use App\Models\Task;
use App\Enums\TaskState;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'state' => $this->faker->randomElement(TaskState::cases()),
            'weight' => $this->faker->numberBetween(0, 100),
            'project_module_id' => \App\Models\ProjectModule::factory(),
        ];
    }
}
