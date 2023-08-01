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
            ['viewAny']
        );

        Provider::factory()->count(5)->create();

        $response = $this->get('/api/providers');

        $response->assertStatus(200);
    }
}
