<?php

namespace Database\Factories;

use App\Models\FoodCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class FoodCategoryFactory extends Factory
{
    protected $model = FoodCategory::class;

    public function definition()
    {
        return [
            'category_name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'user_id' => \App\Models\User::factory(), 
        ];
    }
}
