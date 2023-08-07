<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Company;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $providerable = Company::factory()->create();
        $providerable->address()->create(Address::factory()->make()->toArray());
        return [
            'providerable_id' => $providerable->id,
            'providerable_type' => $providerable->getMorphClass(),
        ];
    }
}
