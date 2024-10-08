<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IsoTask;
use App\Models\Category;
use Faker\Factory as FakerFactory;

class IsoTaskSeeder extends Seeder
{
    public function run()
    {
        $faker = FakerFactory::create();
        $categories = Category::all();

        foreach ($categories as $category) {
            $tasksToCreate = 3;

            for ($i = 0; $i < $tasksToCreate; $i++) {
                IsoTask::create([
                    'name' => $faker->sentence(3),
                    'weight' => $faker->randomFloat(2, 1, 100),
                    'category_id' => $category->id,
                    'description' => $faker->paragraph(),
                    'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                    'updated_at' => $faker->dateTimeBetween('-1 year', 'now'),
                ]);
            }
        }
    }
}
