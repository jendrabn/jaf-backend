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

class NewsletterWebviewGetTest extends ApiTestCase
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
    public function can_render_newsletter_webview()
    {
        $receipt = $this->createReceipt();

        $response = $this->getJson('/api/newsletter/webview/'.$receipt->id.'/'.$receipt->subscriber->token);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.campaign.name', 'Campaign')
            ->assertJsonPath('data.campaign.subject', 'Subject')
            ->assertJsonPath('data.subscriber.email', 'subscriber@example.com')
            ->assertJsonPath('data.subscriber.name', 'Subscriber');

        $this->assertSame(CampaignReceiptStatus::Opened->value, $receipt->fresh()->status->value);
    }

    #[Test]
    public function returns_not_found_for_invalid_newsletter_webview_data()
    {
        $response = $this->getJson('/api/newsletter/webview/999/invalid-token');

        $response->assertNotFound()
            ->assertJsonPath('error', 'Invalid webview data');
    }
}
