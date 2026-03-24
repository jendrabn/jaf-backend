<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class NotificationFcmTokenPutTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_user_cannot_update_fcm_token()
    {
        $response = $this->putJson('/api/notifications/fcm-token');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function can_update_fcm_token()
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/notifications/fcm-token', [
            'fcm_token' => 'token-123',
        ]);

        $response->assertOk()
            ->assertJson([
                'data' => true,
                'message' => 'FCM token updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'fcm_token' => 'token-123',
        ]);
    }

    #[Test]
    public function validates_fcm_token()
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/notifications/fcm-token', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['fcm_token']);
    }
}
