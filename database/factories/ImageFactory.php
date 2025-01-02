<?php

namespace Database\Factories;

use App\Models\Image;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Image::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'image_path' => $this->faker->imageUrl(),
            'mime_type' => $this->faker->mimeType(),
            'name' => $this->faker->word(),
            'imagable_id' => Restaurant::factory(),
            'imagable_type' => Restaurant::class,
        ];
    }
}
