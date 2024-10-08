<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IsoTask;
use App\Models\Category;

class IsoTaskSeeder extends Seeder
{
    public function run()
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            IsoTask::create([
                'name' => 'ISO Task for ' . $category->name,
                'description' => 'Description for ISO Task in ' . $category->name,
                'category_id' => $category->id,
                'weight' => rand(1, 100),
            ]);
        }
    }
}
