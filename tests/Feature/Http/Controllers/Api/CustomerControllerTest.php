<?php

namespace Http\Controllers\Api;

use App\Models\Customer;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{

    public function test_index_returns_200_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['viewAny']
        );

        $response = $this->get('/api/customers');

        $response->assertStatus(200);
    }

    public function test_index_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $response = $this->get('/api/customers');

        $response->assertStatus(403);
    }

    public function test_show_returns_200_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['read']
        );

        $customer = Customer::factory()->create();

        $response = $this->get('/api/customers/' . $customer->id);

        $response->assertStatus(200);
    }

    public function test_show_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $customer = Customer::factory()->create();

        $response = $this->get('/api/customers/' . $customer->id);

        $response->assertStatus(403);
    }

    public function test_show_returns_404_status_code_when_customer_does_not_exist(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['read']
        );

        $response = $this->get('/api/customers/1');

        $response->assertStatus(404);
    }

    public function test_store_returns_200_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['create']
        );

        $response = $this->post('/api/customers', [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->email(),
            'company_name' => fake()->company(),
            'phone' => fake()->phoneNumber(),
            'address[street]' => fake()->streetAddress(),
            'address[street_complement]' => fake()->streetSuffix(),
            'address[city]' => fake()->city(),
            'address[zip_code]' => fake()->postcode(),
            'address[country]' => fake()->country(),
        ]);

        $response->assertStatus(200);
    }

    public function test_store_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $response = $this->post('/api/customers', [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->email(),
            'company_name' => fake()->company(),
            'phone' => fake()->phoneNumber(),
            'address[street]' => fake()->streetAddress(),
            'address[street_complement]' => fake()->streetSuffix(),
            'address[city]' => fake()->city(),
            'address[zip_code]' => fake()->postcode(),
            'address[country]' => fake()->country(),
        ]);

        $response->assertStatus(403);
    }

//    public function test_update_returns_200_status_code_when_user_is_authorized(): void
//    {
//        Sanctum::actingAs(
//            User::factory()->create(),
//            ['update']
//        );
//
//        $customer = Customer::factory()->create();
//
//        $response = $this->put('/api/customers/' . $customer->id, [
//            'first_name' => fake()->firstName(),
//            'last_name' => fake()->lastName(),
//            'email' => fake()->email(),
//            'company_name' => fake()->company(),
//            'phone' => fake()->phoneNumber(),
//            'address[street]' => fake()->streetAddress(),
//            'address[street_complement]' => fake()->streetSuffix(),
//            'address[city]' => fake()->city(),
//            'address[zip_code]' => fake()->postcode(),
//            'address[country]' => fake()->country(),
//        ]);
//
//        $response->assertStatus(200);
//    }

    public function test_update_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $customer = Customer::factory()->create();

        $response = $this->put('/api/customers/' . $customer->id, [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->email(),
            'company_name' => fake()->company(),
            'phone' => fake()->phoneNumber(),
            'address[street]' => fake()->streetAddress(),
            'address[street_complement]' => fake()->streetSuffix(),
            'address[city]' => fake()->city(),
            'address[zip_code]' => fake()->postcode(),
            'address[country]' => fake()->country(),
        ]);

        $response->assertStatus(403);
    }

    public function test_update_returns_404_status_code_when_customer_does_not_exist(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['update']
        );

        $response = $this->put('/api/customers/1', [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->email(),
            'company_name' => fake()->company(),
            'phone' => fake()->phoneNumber(),
            'address[street]' => fake()->streetAddress(),
            'address[street_complement]' => fake()->streetSuffix(),
            'address[city]' => fake()->city(),
            'address[zip_code]' => fake()->postcode(),
            'address[country]' => fake()->country(),
        ]);

        $response->assertStatus(404);
    }

    public function test_destroy_returns_202_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['delete']
        );

        $customer = Customer::factory()->create();

        $response = $this->delete('/api/customers/' . $customer->id);

        $response->assertStatus(202);
    }

}
