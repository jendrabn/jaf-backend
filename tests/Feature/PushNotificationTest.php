<?php

namespace Tests\Feature;

use App\Jobs\SendPushNotificationJob;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\FirebaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test FCM token update endpoint
     */
    public function test_user_can_update_fcm_token(): void
    {
        $token = 'test_fcm_token_12345';

        $response = $this->actingAs($this->user)
            ->putJson('/api/user/fcm-token', [
                'fcm_token' => $token,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => true,
                'message' => 'FCM token updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'fcm_token' => $token,
        ]);
    }

    /**
     * Test FCM token validation
     */
    public function test_fcm_token_validation(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson('/api/user/fcm-token', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fcm_token']);
    }

    /**
     * Test that creating notification dispatches push notification job
     */
    public function test_creating_notification_dispatches_push_notification_job(): void
    {
        Queue::fake();

        $notification = UserNotification::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Queue::assertPushed(SendPushNotificationJob::class, function ($job) use ($notification) {
            return $job->notification->id === $notification->id;
        });
    }

    /**
     * Test push notification job with valid FCM token
     */
    public function test_push_notification_job_with_valid_token(): void
    {
        $this->user->update(['fcm_token' => 'valid_fcm_token']);

        $notification = UserNotification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Notification',
            'body' => 'Test body',
            'category' => 'transaction',
            'level' => 'info',
            'url' => null,
            'icon' => null,
        ]);

        $this->mock(FirebaseService::class, function ($mock) use ($notification) {
            $mock->shouldReceive('sendNotification')
                ->once()
                ->andReturn([
                    'success' => true,
                    'data' => ['name' => 'projects/test/messages/12345'],
                    'status' => 200,
                ]);
        });

        $job = new SendPushNotificationJob($notification);
        $job->handle();
    }

    /**
     * Test push notification job with invalid FCM token
     */
    public function test_push_notification_job_with_invalid_token(): void
    {
        $this->user->update(['fcm_token' => 'invalid_fcm_token']);

        $notification = UserNotification::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->mock(FirebaseService::class, function ($mock) {
            $mock->shouldReceive('sendNotification')
                ->once()
                ->andReturn([
                    'success' => false,
                    'data' => ['error' => 'UNREGISTERED'],
                    'status' => 404,
                ]);
        });

        $job = new SendPushNotificationJob($notification);
        $job->handle();

        $this->user->refresh();
        $this->assertNull($this->user->fcm_token);
    }

    /**
     * Test push notification job without FCM token
     */
    public function test_push_notification_job_without_fcm_token(): void
    {
        $notification = UserNotification::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->mock(FirebaseService::class, function ($mock) {
            $mock->shouldNotReceive('sendNotification');
        });

        $job = new SendPushNotificationJob($notification);
        $job->handle();
    }

    /**
     * Test push notification job with meta data
     */
    public function test_push_notification_job_with_meta_data(): void
    {
        $this->user->update(['fcm_token' => 'valid_fcm_token']);

        $notification = UserNotification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Order Update',
            'body' => 'Your order has been shipped',
            'meta' => [
                'order_id' => 123,
                'tracking_number' => 'TRK123456',
            ],
        ]);

        $this->mock(FirebaseService::class, function ($mock) {
            $mock->shouldReceive('sendNotification')
                ->once()
                ->andReturn([
                    'success' => true,
                    'data' => ['name' => 'projects/test/messages/12345'],
                    'status' => 200,
                ]);
        });

        $job = new SendPushNotificationJob($notification);
        $job->handle();
    }
}
