<?php

namespace Tests\Feature\Api;

use App\Enums\SubscriberStatus;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class NewsletterUnsubscribeGetTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_unsubscribe_subscriber_by_token()
    {
        $subscriber = Subscriber::query()->create([
            'email' => 'subscriber@example.com',
            'name' => 'Subscriber',
            'token' => Str::random(64),
            'status' => SubscriberStatus::Subscribed->value,
            'subscribed_at' => now(),
        ]);

        $response = $this->getJson('/api/newsletter/unsubscribe/'.$subscriber->token);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'You have been successfully unsubscribed from our newsletter');

        $this->assertSame(SubscriberStatus::Unsubscribed->value, $subscriber->fresh()->status);
    }

    #[Test]
    public function returns_not_found_for_invalid_unsubscribe_token()
    {
        $response = $this->getJson('/api/newsletter/unsubscribe/invalid-token');

        $response->assertNotFound()
            ->assertJsonPath('success', false);
    }
}
