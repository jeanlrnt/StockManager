<?php

namespace Http\Controllers\Api;

use App\Models\Provider;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use tests\TestCase;

class ProviderControllerTest extends TestCase
{

    public function test_index_returns_200_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-any']
        );

        Provider::all()->each->delete();

        Provider::factory()->count(5)->create();

        $response = $this->get('/api/providers');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    public function test_index_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $response = $this->get('/api/providers');

        $response->assertStatus(403);
    }

    public function test_show_returns_200_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['read']
        );

        $provider = Provider::factory()->create();

        $response = $this->get('/api/providers/' . $provider->id);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $provider->id,
            'name' => $provider->name,
            'phone' => $provider->phone,
            'email' => $provider->email,
        ]);
    }

    public function test_show_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $provider = Provider::factory()->create();

        $response = $this->get('/api/providers/' . $provider->id);

        $response->assertStatus(403);
    }

    public function test_show_returns_404_status_code_when_provider_is_not_found(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['read']
        );

        $response = $this->get('/api/providers/1');

        $response->assertStatus(404);
    }

    public function test_store_returns_201_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['create']
        );

        $provider = Provider::factory()->make();

        $response = $this->post('/api/providers', [
            'name' => $provider->name,
            'phone' => $provider->phone,
            'email' => $provider->email,
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'name' => $provider->name,
            'phone' => $provider->phone,
            'email' => $provider->email,
        ]);
    }

    public function test_store_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $provider = Provider::factory()->make();

        $response = $this->post('/api/providers', [
            'name' => $provider->name,
            'phone' => $provider->phone,
            'email' => $provider->email,
        ]);

        $response->assertStatus(403);
    }

    public function test_store_returns_422_status_code_when_validation_fails(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['create']
        );

        $response = $this->post('/api/providers', []);

        $response->assertStatus(422);
    }

    public function test_update_returns_202_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['update']
        );

        $provider = Provider::factory()->create();

        $newProvider = [
            'name' => fake()->name,
            'phone' => fake()->phoneNumber,
            'email' => fake()->email,
        ];

        $response = $this->put('/api/providers/' . $provider->id, $newProvider);

        $response->assertStatus(202);
        $response->assertJsonFragment($newProvider);
    }

    public function test_update_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $provider = Provider::factory()->create();

        $response = $this->put('/api/providers/' . $provider->id);

        $response->assertStatus(403);
    }

    public function test_update_returns_422_status_code_when_validation_fails(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['update']
        );

        $provider = Provider::factory()->create();

        $response = $this->put('/api/providers/' . $provider->id, ['email' => 'invalid-email']);

        $response->assertStatus(422);
    }

    public function test_update_returns_404_status_code_when_provider_is_not_found(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['update']
        );

        $response = $this->put('/api/providers/1');

        $response->assertStatus(404);
    }

    public function test_destroy_returns_202_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['delete']
        );

        $provider = Provider::factory()->create();

        $response = $this->delete('/api/providers/' . $provider->id);

        $response->assertStatus(202);
    }

    public function test_destroy_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $provider = Provider::factory()->create();

        $response = $this->delete('/api/providers/' . $provider->id);

        $response->assertStatus(403);
    }

    public function test_destroy_returns_404_status_code_when_provider_is_not_found(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['delete']
        );

        $response = $this->delete('/api/providers/1');

        $response->assertStatus(404);
    }

    public function test_restore_returns_202_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['restore']
        );

        $provider = Provider::factory()->create([
            'deleted_at' => now(),
        ]);

        $response = $this->post('/api/providers/' . $provider->id . '/restore');

        $response->assertStatus(202);
        $response->assertJsonFragment([
            'id' => $provider->id,
        ]);
    }

    public function test_restore_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $provider = Provider::factory()->create([
            'deleted_at' => now(),
        ]);

        $response = $this->post('/api/providers/' . $provider->id . '/restore');

        $response->assertStatus(403);
    }

    public function test_restore_returns_404_status_code_when_provider_is_not_found(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['restore']
        );

        $response = $this->post('/api/providers/1/restore');

        $response->assertStatus(404);
    }

    public function test_force_delete_returns_202_status_code_when_user_is_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['force-delete']
        );

        $provider = Provider::factory()->create([
            'deleted_at' => now(),
        ]);

        $response = $this->delete('/api/providers/' . $provider->id . '/force-delete');

        $response->assertStatus(202);
    }

    public function test_force_delete_returns_403_status_code_when_user_is_not_authorized(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['missing-permission']
        );

        $provider = Provider::factory()->create([
            'deleted_at' => now(),
        ]);

        $response = $this->delete('/api/providers/' . $provider->id . '/force-delete');

        $response->assertStatus(403);
    }

    public function test_force_delete_returns_404_status_code_when_provider_is_not_found(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['force-delete']
        );

        $response = $this->delete('/api/providers/1/force-delete');

        $response->assertStatus(404);
    }
}
