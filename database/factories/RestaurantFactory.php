<?php

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Restaurant>
 */
class RestaurantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'location' => $this->faker->address(),
            'opening_hours' => $this->faker->time('H:i:s'),
            'closing_hours' => $this->faker->time('H:i:s'),
            'rating' => $this->faker->numberBetween(1, 5),
            'website' => $this->faker->url(),
            'description' => $this->faker->sentence(),
        ];
    }
}
