<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\NewsletterController;
use App\Http\Requests\Api\SubscribeRequest;
use App\Jobs\SendSubscribeNotificationJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class NewsletterSubscribePostTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function subscribe_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(
            NewsletterController::class,
            'subscribe',
            SubscribeRequest::class
        );
    }

    #[Test]
    public function subscribe_request_has_the_correct_validation_rules()
    {
        $this->assertValidationRules([
            'email' => 'required|email|unique:subscribers,email',
            'name' => 'nullable|string|max:255',
        ], (new SubscribeRequest)->rules());
    }

    #[Test]
    public function can_subscribe_to_newsletter()
    {
        Queue::fake();

        $response = $this->postJson('/api/newsletter/subscribe', [
            'email' => 'subscriber@example.com',
            'name' => 'Subscriber Name',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'subscriber@example.com');

        $this->assertDatabaseHas('subscribers', [
            'email' => 'subscriber@example.com',
            'name' => 'Subscriber Name',
            'status' => 'subscribed',
        ]);

        Queue::assertPushed(SendSubscribeNotificationJob::class);
    }

    #[Test]
    public function rate_limits_newsletter_subscription_requests()
    {
        Queue::fake();

        $payload = [
            'email' => 'subscriber@example.com',
            'name' => 'Subscriber Name',
        ];

        $firstResponse = $this->postJson('/api/newsletter/subscribe', $payload);
        $secondResponse = $this->postJson('/api/newsletter/subscribe', [
            'email' => 'subscriber-2@example.com',
            'name' => 'Another Subscriber',
        ]);

        $firstResponse->assertCreated();
        $secondResponse->assertStatus(429)
            ->assertJsonPath('success', false);
    }
}
