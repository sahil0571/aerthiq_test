<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DeductionFactory extends Factory
{
    public function definition()
    {
        return [
            'employee_id' => \App\Models\Employee::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 2000),
            'description' => $this->faker->sentence,
            'date' => $this->faker->date,
            'deduction_type' => $this->faker->randomElement(['tax', 'insurance', 'loan', 'advance', 'other']),
            'is_recurring' => $this->faker->boolean(30),
            'monthly_deduction' => $this->faker->optional()->randomFloat(2, 50, 2000),
            'financial_year' => $this->faker->optional()->randomElement(['FY2024', 'FY2023', 'FY2025']),
        ];
    }
}