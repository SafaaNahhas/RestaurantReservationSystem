<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\FoodCategory;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteFactory extends Factory
{
    protected $model = \App\Models\Favorite::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Determine which type will be chosen at random
        $favorableType = $this->faker->randomElement([FoodCategory::class, Table::class]);

        $favorableId = null;
        if ($favorableType == FoodCategory::class) {
            $favorableId = FoodCategory::factory(); // Create a new food category if the favorite is a FoodCategory
        } elseif ($favorableType == Table::class) {
            $favorableId = Table::factory(); // Create a new table if the favorite is a Table
        }

        return [
            'user_id' => User::factory(),
            'favorable_type' => $favorableType,
            'favorable_id' => $favorableId,
        ];
    }
}
