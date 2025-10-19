<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class AuthLogoutDeleteTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_user_cannot_logout()
    {
        $response = $this->deleteJson('/api/auth/logout');

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function can_logout()
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/auth/logout');

        $response->assertOk()
            ->assertJson(['data' => true]);

        $this->assertCount(0, $user->fresh()->tokens);
    }
}
