<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition()
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('PRJ###')),
            'name' => $this->faker->company . ' Project',
            'description' => $this->faker->optional()->sentence,
            'start_date' => $this->faker->optional()->date(),
            'end_date' => $this->faker->optional()->date(),
            'budget' => $this->faker->randomFloat(2, 1000, 100000),
            'status' => $this->faker->randomElement(['planned', 'active', 'completed', 'on_hold']),
            'client_name' => $this->faker->optional()->company,
        ];
    }
}