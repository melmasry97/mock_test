<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\ProjectModule;
use App\Enums\TaskState;

class TaskSeeder extends Seeder
{
    public function run()
    {
        $projectModules = ProjectModule::all();
        $states = TaskState::cases();

        foreach ($projectModules as $projectModule) {
            foreach ($states as $state) {
                Task::factory()->create([
                    'project_module_id' => $projectModule->id,
                    'state' => $state,
                ]);
            }
        }
    }
}
