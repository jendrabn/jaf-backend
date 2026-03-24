<?php

namespace Tests\Feature\Api;

use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class NotificationReadAllPutTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_user_cannot_mark_all_notifications_as_read()
    {
        $response = $this->putJson('/api/notifications/read-all');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function can_mark_all_notifications_as_read()
    {
        $user = $this->createUser();

        UserNotification::factory()->count(2)->for($user)->unread()->create();
        UserNotification::factory()->for($user)->read()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/notifications/read-all');

        $response->assertOk()
            ->assertJson([
                'data' => true,
                'message' => 'All notifications marked as read',
                'count' => 2,
            ]);

        $this->assertSame(0, $user->notifications()->unread()->count());
    }
}
