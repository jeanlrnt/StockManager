<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->companyEmail,
            'website' => $this->faker->url,
            'industry' => $this->faker->word,
            'number_of_employees' => $this->faker->numberBetween(1, 100),
            'annual_revenue' => $this->faker->numberBetween(1000, 1000000),
            'description' => $this->faker->paragraph,
        ];
    }
}
