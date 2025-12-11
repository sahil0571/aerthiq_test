<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition()
    {
        return [
            'date' => $this->faker->date,
            'description' => $this->faker->sentence,
            'amount' => $this->faker->randomFloat(2, 1, 5000),
            'transaction_type' => $this->faker->randomElement(['debit', 'credit']),
            'account_id' => \App\Models\Account::factory(),
            'project_id' => null, // Set separately when needed
            'employee_id' => null, // Set separately when needed
            'category' => $this->faker->optional()->word,
            'reference' => $this->faker->optional()->bothify('REF###'),
            'notes' => $this->faker->optional()->sentence,
            'financial_year' => $this->faker->optional()->randomElement(['2023-2024', '2024-2025', '2025-2026']),
        ];
    }
}