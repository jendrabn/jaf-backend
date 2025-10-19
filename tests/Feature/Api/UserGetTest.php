<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class UserGetTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_user_cannot_get_profile()
    {
        $response = $this->putJson('/api/user');

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function can_get_profile()
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertOk()
            ->assertJson(['data' => $this->formatUserData($user)]);
    }
}
