<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition()
    {
        return [
            'employee_code' => strtoupper($this->faker->unique()->bothify('EMP###')),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->optional()->phoneNumber,
            'department' => $this->faker->optional()->department,
            'position' => $this->faker->optional()->jobTitle,
            'hire_date' => $this->faker->optional()->date(),
            'salary' => $this->faker->randomFloat(2, 30000, 120000),
            'is_active' => true,
            'project_id' => null, // Set separately when needed
        ];
    }
}