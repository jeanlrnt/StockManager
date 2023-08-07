<?php

namespace Helpers;

use App\Models\Customer;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_index_with_pagination_returns_default_20_records(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-any']
        );

        Customer::all()->each->delete();

        Customer::factory()->count(30)->create();

        $response = $this->get('/api/customers');

        $response->assertJsonCount(20, 'data');
    }

    public function test_index_with_pagination_returns_10_records(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-any']
        );

        Customer::all()->each->delete();

        Customer::factory()->count(30)->create();

        $response = $this->get('/api/customers?limit=10');

        $response->assertJsonCount(10, 'data');
    }

    public function test_index_with_pagination_returns_all_records_when_limit_is_greater_than_total_records(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-any']
        );

        Customer::all()->each->delete();

        Customer::factory()->count(10)->create();

        $response = $this->get('/api/customers?limit=50');

        $response->assertJsonCount(10, 'data');
    }

    public function test_index_with_pagination_returns_10_records_of_each_page(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-any']
        );

        Customer::all()->each->delete();

        Customer::factory()->count(20)->create();

        $response = $this->get('/api/customers?limit=10&page=1');
        $response->assertJsonCount(10, 'data');

        $response = $this->get('/api/customers?limit=10&page=2');
        $response->assertJsonCount(10, 'data');
    }

    public function test_index_with_pagination_returns_10_records_of_each_page_and_1_record_of_last_page(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-any']
        );

        Customer::all()->each->delete();

        Customer::factory()->count(21)->create();

        $response = $this->get('/api/customers?limit=10&page=1');
        $response->assertJsonCount(10, 'data');

        $response = $this->get('/api/customers?limit=10&page=2');
        $response->assertJsonCount(10, 'data');

        $response = $this->get('/api/customers?limit=10&page=3');
        $response->assertJsonCount(1, 'data');
    }

    public function test_index_with_pagination_returns_default_20_records_when_limit_is_not_a_number_or_less_than_1(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-any']
        );

        Customer::all()->each->delete();

        Customer::factory()->count(30)->create();

        $response = $this->get('/api/customers?limit=abc');
        $response->assertJsonCount(20, 'data');

        $response = $this->get('/api/customers?limit=0');
        $response->assertJsonCount(20, 'data');
    }

    public function test_index_with_pagination_returns_first_records_when_page_is_not_a_number_or_less_than_1_and_limit_is_greater_than_0(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-any']
        );

        Customer::all()->each->delete();

        Customer::factory()->count(25)->create();

        $response = $this->get('/api/customers?page=abc');
        $response->assertJsonCount(20, 'data');

        $response = $this->get('/api/customers?limit=10&page=abc');
        $response->assertJsonCount(10, 'data');

        $response = $this->get('/api/customers?page=0');
        $response->assertJsonCount(20, 'data');

        $response = $this->get('/api/customers?limit=10&page=0');
        $response->assertJsonCount(10, 'data');
    }

    public function test_index_with_pagination_returns_last_page_with_default_pagination_when_page_is_greater_than_total_pages(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-any']
        );

        Customer::all()->each->delete();

        Customer::factory()->count(30)->create();

        $response = $this->get('/api/customers?page=3');
        $response->assertJsonCount(10, 'data');

        $response = $this->get('/api/customers?limit=0&page=3');
        $response->assertJsonCount(10, 'data');

        $response = $this->get('/api/customers?limit=abc&page=3');
        $response->assertJsonCount(10, 'data');
    }

    public function test_index_with_pagination_returns_structured_data(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-any']
        );

        Customer::all()->each->delete();

        $customer = Customer::factory()->count(10)->create();

        $response = $this->get('/api/customers?limit=10');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                ]
            ],
            'links' => [
                'first',
                'last',
                'self',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'last_page',
                'from',
                'to',
                'per_page',
                'total',
                'request'
            ]
        ]);
    }

    public function test_index_with_pagination_returns_links(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-any']
        );

        Customer::all()->each->delete();

        $customer = Customer::factory()->count(10)->create();

        $response = $this->get('/api/customers?limit=10');
        $response->assertJsonFragment([
            'first' => 'http://localhost/api/customers?limit=10&page=1',
            'last' => 'http://localhost/api/customers?limit=10&page=1',
            'self' => 'http://localhost/api/customers?limit=10&page=1',
            'prev' => null,
            'next' => null,
        ]);
    }

    public function test_index_with_pagination_returns_links_when_page_is_greater_than_1(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-any']
        );

        Customer::all()->each->delete();

        $customer = Customer::factory()->count(30)->create();

        $response = $this->get('/api/customers?limit=10&page=2');
        $response->assertJsonFragment([
            'first' => 'http://localhost/api/customers?limit=10&page=1',
            'last' => 'http://localhost/api/customers?limit=10&page=3',
            'self' => 'http://localhost/api/customers?limit=10&page=2',
            'prev' => 'http://localhost/api/customers?limit=10&page=1',
            'next' => 'http://localhost/api/customers?limit=10&page=3',
        ]);

        $response = $this->get('/api/customers?limit=0&page=2');
        $response->assertJsonFragment([
            'first' => 'http://localhost/api/customers?limit=20&page=1',
            'last' => 'http://localhost/api/customers?limit=20&page=2',
            'self' => 'http://localhost/api/customers?limit=20&page=2',
            'prev' => 'http://localhost/api/customers?limit=20&page=1',
            'next' => null,
        ]);

        $response = $this->get('/api/customers?limit=abc&page=2');
        $response->assertJsonFragment([
            'first' => 'http://localhost/api/customers?limit=20&page=1',
            'last' => 'http://localhost/api/customers?limit=20&page=2',
            'self' => 'http://localhost/api/customers?limit=20&page=2',
            'prev' => 'http://localhost/api/customers?limit=20&page=1',
            'next' => null,
        ]);

        $response = $this->get('/api/customers?limit=abc&page=abc');
        $response->assertJsonFragment([
            'first' => 'http://localhost/api/customers?limit=20&page=1',
            'last' => 'http://localhost/api/customers?limit=20&page=2',
            'self' => 'http://localhost/api/customers?limit=20&page=1',
            'prev' => null,
            'next' => 'http://localhost/api/customers?limit=20&page=2',
        ]);
    }

    public function test_index_with_pagination_returns_meta_data(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-any']
        );

        Customer::all()->each->delete();

        $customer = Customer::factory()->count(25)->create();

        $response = $this->get('/api/customers?limit=10&page=2');
        $response->assertJsonFragment([
            'current_page' => 2,
            'last_page' => 3,
            'from' => 11,
            'to' => 20,
            'per_page' => 10,
            'total' => 25,
            'request' => '/api/customers?limit=10&page=2',
        ]);

        $response = $this->get('/api/customers?limit=0&page=2');
        $response->assertJsonFragment([
            'current_page' => 2,
            'last_page' => 2,
            'from' => 21,
            'to' => 25,
            'per_page' => 20,
            'total' => 25,
            'request' => '/api/customers?limit=0&page=2',
        ]);

        $response = $this->get('/api/customers?limit=abc&page=2');
        $response->assertJsonFragment([
            'current_page' => 2,
            'last_page' => 2,
            'from' => 21,
            'to' => 25,
            'per_page' => 20,
            'total' => 25,
            'request' => '/api/customers?limit=abc&page=2',
        ]);

        $response = $this->get('/api/customers?limit=abc&page=abc');
        $response->assertJsonFragment([
            'current_page' => 1,
            'last_page' => 2,
            'from' => 1,
            'to' => 20,
            'per_page' => 20,
            'total' => 25,
            'request' => '/api/customers?limit=abc&page=abc',
        ]);
    }

}
