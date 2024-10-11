<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IsoTask;
use App\Models\Project;

class IsoTaskSeeder extends Seeder
{
    public function run(): void
    {
        // Create 5 projects if they don't exist
        $projects = Project::count() < 5 ? Project::factory(5)->create() : Project::all();

        // Create 20 ISO tasks
        IsoTask::factory(20)->make()->each(function ($isoTask) use ($projects) {
            $isoTask->project_id = $projects->random()->id;
            $isoTask->save();
        });
    }
}
