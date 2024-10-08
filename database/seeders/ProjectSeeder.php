<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Category;

class ProjectSeeder extends Seeder
{
    public function run()
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            Project::factory(3)->create([
                'category_id' => $category->id,
            ]);
        }
    }
}
