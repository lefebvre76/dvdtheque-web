<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Box>
 */
class BoxFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['DVD', 'BR', 'UHD']),
            'bar_code' => fake()->ean13(),
            'title' => fake()->realText(),
            'original_title' => fake()->realText(),
            'year' => fake()->year(),
            'synopsis' => fake()->paragraph()
        ];
    }
}
