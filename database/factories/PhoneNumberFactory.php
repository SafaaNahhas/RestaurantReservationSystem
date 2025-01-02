<?php

namespace Database\Factories;

use App\Models\PhoneNumber;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PhoneNumber>
 */
class PhoneNumberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'PhoneNumber' => $this->faker->phoneNumber(),
            'description' => $this->faker->sentence(),
            'restaurant_id' => Restaurant::factory(), // Assumes RestaurantFactory exists
        ];
    }
}
