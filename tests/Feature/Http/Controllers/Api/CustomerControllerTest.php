<?php

namespace Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_200_status_code_when_user_is_authorized(): void
    {
        $user = User::factory()->create();

        $user->createToken(Str::uuid(), ['viewAny']);

        $response = $this->actingAs($user)->getJson('/api/customers');

        $response->assertStatus(200);
    }
}
