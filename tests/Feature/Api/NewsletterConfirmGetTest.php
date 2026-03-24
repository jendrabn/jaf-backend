<?php

namespace Tests\Feature\Api;

use App\Enums\SubscriberStatus;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class NewsletterConfirmGetTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_confirm_subscription_by_token()
    {
        $subscriber = Subscriber::query()->create([
            'email' => 'subscriber@example.com',
            'name' => 'Subscriber',
            'token' => Str::random(64),
            'status' => SubscriberStatus::Pending->value,
            'subscribed_at' => null,
        ]);

        $response = $this->getJson('/api/newsletter/confirm/'.$subscriber->token);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Your newsletter subscription has been confirmed');

        $this->assertSame(SubscriberStatus::Subscribed->value, $subscriber->fresh()->status);
        $this->assertNotNull($subscriber->fresh()->subscribed_at);
    }

    #[Test]
    public function returns_not_found_for_invalid_confirmation_token()
    {
        $response = $this->getJson('/api/newsletter/confirm/invalid-token');

        $response->assertNotFound()
            ->assertJsonPath('success', false);
    }
}
