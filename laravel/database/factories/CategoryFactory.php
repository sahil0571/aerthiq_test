<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word,
            'type' => $this->faker->randomElement(['income', 'expense', 'asset', 'liability']),
            'description' => $this->faker->optional()->sentence,
            'is_active' => true,
        ];
    }
}