<?php

namespace Tests\Feature\Api;

use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class NotificationUnreadCountGetTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_user_cannot_get_unread_notification_count()
    {
        $response = $this->getJson('/api/notifications/unread-count');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function can_get_unread_notification_count()
    {
        $user = $this->createUser();

        UserNotification::factory()->count(3)->for($user)->unread()->create();
        UserNotification::factory()->count(2)->for($user)->read()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/notifications/unread-count');

        $response->assertOk()
            ->assertJson(['data' => 3]);
    }
}
