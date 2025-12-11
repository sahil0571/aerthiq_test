<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    public function definition()
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('ACC###')),
            'name' => $this->faker->company . ' ' . $this->faker->word,
            'type' => $this->faker->randomElement(['asset', 'liability', 'equity', 'income', 'expense']),
            'category' => $this->faker->optional()->word,
            'opening_balance' => $this->faker->randomFloat(2, 0, 10000),
            'is_active' => true,
        ];
    }
}