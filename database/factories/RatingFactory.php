<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Rating;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rating>
 */
class RatingFactory extends Factory
{

    protected $model = Rating::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // إنشاء مستخدم جديد عشوائي
            'reservation_id' => Reservation::factory(), // إنشاء حجز جديد عشوائي
            'rating' => $this->faker->numberBetween(1, 5), // تصنيف عشوائي بين 1 و 5
            'comment' => $this->faker->sentence(), // تعليق عشوائي
        ];
    }
}
