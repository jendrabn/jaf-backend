<?php

namespace Tests\Feature\Api;

use App\Enums\CampaignReceiptStatus;
use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\CampaignReceipt;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class NewsletterTrackClickGetTest extends ApiTestCase
{
    use RefreshDatabase;

    private function createReceipt(): CampaignReceipt
    {
        $subscriber = Subscriber::query()->create([
            'email' => 'subscriber@example.com',
            'name' => 'Subscriber',
            'token' => Str::random(64),
            'status' => 'subscribed',
            'subscribed_at' => now(),
        ]);
        $campaign = Campaign::query()->create([
            'name' => 'Campaign',
            'subject' => 'Subject',
            'content' => 'Body',
            'status' => CampaignStatus::SENT->value,
        ]);

        return CampaignReceipt::query()->create([
            'campaign_id' => $campaign->id,
            'subscriber_id' => $subscriber->id,
            'status' => CampaignReceiptStatus::Sent->value,
            'sent_at' => now(),
        ]);
    }

    #[Test]
    public function can_track_newsletter_click_and_return_redirect_url()
    {
        $receipt = $this->createReceipt();

        $response = $this->getJson(
            '/api/newsletter/track/click/'.$receipt->id.'/'.$receipt->subscriber->token.'?url='.urlencode('https://example.com/promo')
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('redirect_url', 'https://example.com/promo');

        $this->assertSame(CampaignReceiptStatus::Clicked->value, $receipt->fresh()->status->value);
        $this->assertNotNull($receipt->fresh()->clicked_at);
    }

    #[Test]
    public function returns_not_found_for_invalid_newsletter_click_tracking_data()
    {
        $response = $this->getJson('/api/newsletter/track/click/999/invalid-token');

        $response->assertNotFound()
            ->assertJsonPath('error', 'Invalid tracking data');
    }
}
