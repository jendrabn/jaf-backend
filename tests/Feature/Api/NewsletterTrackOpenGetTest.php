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

class NewsletterTrackOpenGetTest extends ApiTestCase
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
    public function can_track_newsletter_open()
    {
        $receipt = $this->createReceipt();

        $response = $this->get('/api/newsletter/track/open/'.$receipt->id.'/'.$receipt->subscriber->token);

        $response->assertOk();

        $this->assertSame(CampaignReceiptStatus::Opened->value, $receipt->fresh()->status->value);
        $this->assertNotNull($receipt->fresh()->opened_at);
    }

    #[Test]
    public function ignores_invalid_newsletter_open_tracking_data()
    {
        $response = $this->get('/api/newsletter/track/open/999/invalid-token');

        $response->assertOk();
    }
}
