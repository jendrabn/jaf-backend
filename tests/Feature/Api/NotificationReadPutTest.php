<?php

namespace Tests\Feature\Api;

use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class NotificationReadPutTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_user_cannot_mark_notification_as_read()
    {
        $response = $this->putJson('/api/notifications/1/read');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function can_mark_notification_as_read()
    {
        $user = $this->createUser();
        $notification = UserNotification::factory()->for($user)->unread()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/notifications/'.$notification->id.'/read');

        $response->assertOk()
            ->assertJson([
                'data' => true,
                'message' => 'Notification marked as read',
            ]);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    #[Test]
    public function cannot_mark_another_users_notification_as_read()
    {
        $user = $this->createUser();
        $notification = UserNotification::factory()->for($this->createUser())->unread()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/notifications/'.$notification->id.'/read');

        $response->assertForbidden()
            ->assertJsonPath('message', 'Notification not found');
    }
}
