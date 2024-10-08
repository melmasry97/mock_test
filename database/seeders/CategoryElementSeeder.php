<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryElement;
use App\Models\Category;

class CategoryElementSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            CategoryElement::factory(3)->create([
                'category_id' => $category->id,
            ]);
        }
    }
}
