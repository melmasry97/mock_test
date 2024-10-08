<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProjectModule;
use App\Models\Project;
use Faker\Factory as FakerFactory;

class ProjectModuleSeeder extends Seeder
{
    public function run()
    {
        $faker = FakerFactory::create();
        $projects = Project::all();

        foreach ($projects as $project) {
            $remainingWeight = 100;
            $modulesToCreate = 3;

            for ($i = 0; $i < $modulesToCreate; $i++) {
                if ($i == $modulesToCreate - 1) {
                    $weight = $remainingWeight;
                } else {
                    $maxWeight = $remainingWeight - ($modulesToCreate - $i - 1);
                    $weight = $faker->randomFloat(2, 1, max(1, $maxWeight));
                }

                ProjectModule::create([
                    'name' => $faker->words(3, true),
                    'weight' => $weight,
                    'project_id' => $project->id,
                    'category_id' => $project->category_id,
                    'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                    'updated_at' => $faker->dateTimeBetween('-1 year', 'now'),
                ]);

                $remainingWeight -= $weight;
            }
        }
    }
}
