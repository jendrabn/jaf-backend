<?php

namespace Tests\Feature\Api;

use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class NotificationGetTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_user_cannot_get_notifications()
    {
        $response = $this->getJson('/api/notifications');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function can_get_notifications_with_pagination()
    {
        $user = $this->createUser();

        UserNotification::factory()->count(3)->for($user)->create();
        UserNotification::factory()->for($this->createUser())->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/notifications?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('page.total', 3)
            ->assertJsonPath('page.per_page', 2)
            ->assertJsonPath('data.0.user_id', $user->id);
    }
}
