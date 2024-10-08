<?php

namespace Database\Factories;

use App\Models\CategoryElement;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryElementFactory extends Factory
{
    protected $model = CategoryElement::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['percentage', 'number', 'text', 'boolean']),
            'negative' => $this->faker->boolean(),
            'category_id' => Category::inRandomOrder()->first()->id ?? Category::factory(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
