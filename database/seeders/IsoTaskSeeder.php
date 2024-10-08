<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IsoTask;
use App\Models\Category;

class IsoTaskSeeder extends Seeder
{
    public function run(): void
    {
        // Create a category for ISO tasks if it doesn't exist
        $category = Category::firstOrCreate(['name' => 'ISO']);

        // Define 9 ISO tasks with initial weights
        $isoTasks = [
            ['name' => 'ISO Task 1', 'weight' => 0.10],
            ['name' => 'ISO Task 2', 'weight' => 0.15],
            ['name' => 'ISO Task 3', 'weight' => 0.10],
            ['name' => 'ISO Task 4', 'weight' => 0.12],
            ['name' => 'ISO Task 5', 'weight' => 0.13],
            ['name' => 'ISO Task 6', 'weight' => 0.10],
            ['name' => 'ISO Task 7', 'weight' => 0.10],
            ['name' => 'ISO Task 8', 'weight' => 0.10],
            ['name' => 'ISO Task 9', 'weight' => 0.10],
        ];

        $totalWeight = 0;

        foreach ($isoTasks as $task) {
            // Ensure the total weight doesn't exceed 1 (100%)
            $weight = min($task['weight'], 1 - $totalWeight);
            $totalWeight += $weight;

            IsoTask::create([
                'name' => $task['name'],
                'description' => 'Description for ' . $task['name'],
                'category_id' => $category->id,
                'weight' => $weight,
            ]);

            // If we've reached 100%, stop creating tasks
            if ($totalWeight >= 1) {
                break;
            }
        }

        // If the total weight is less than 1, adjust the last task
        if ($totalWeight < 1 && count($isoTasks) > 0) {
            $lastTask = IsoTask::latest()->first();
            $lastTask->update(['weight' => $lastTask->weight + (1 - $totalWeight)]);
        }
    }
}
