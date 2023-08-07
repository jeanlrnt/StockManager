<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        $customerable = Person::factory()->create();
        return [
            'customerable_id' => $customerable->id,
            'customerable_type' => $customerable->getMorphClass(),
        ];
    }
}
