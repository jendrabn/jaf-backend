<?php

namespace Tests\Feature;

use App\Enums\CampaignStatus;
use App\Enums\SubscriberStatus;
use App\Jobs\DispatchCampaignJob;
use App\Jobs\SendCampaignEmailJob;
use App\Models\Campaign;
use App\Models\CampaignReceipt;
use App\Models\Subscriber;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class CampaignNewsletterTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions and grant admin full access, including backoffice.access
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function actingAsAdmin(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);

        return $user;
    }

    #[Test]
    public function can_render_index_datatable(): void
    {
        $this->actingAsAdmin();

        $response = $this->get('/campaigns');

        $response->assertOk();
        // DataTable element id must be present
        $response->assertSee('campaigns-table', false);
    }

    #[Test]
    public function can_create_campaign(): void
    {
        $this->actingAsAdmin();

        $payload = [
            'name' => 'October Promo',
            'subject' => 'Special Discount',
            'content' => '<p>Hello Subscribers</p>',
            'status' => CampaignStatus::DRAFT->value,
            'scheduled_at' => null,
        ];

        $response = $this->post('/campaigns', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('campaigns', [
            'name' => 'October Promo',
            'subject' => 'Special Discount',
            'status' => CampaignStatus::DRAFT->value,
        ]);
    }

    #[Test]
    public function can_queue_send_to_all_subscribers(): void
    {
        $this->actingAsAdmin();
        Queue::fake();

        $campaign = Campaign::create([
            'name' => 'Black Friday',
            'subject' => 'BF Deals',
            'content' => '<p>Deals</p>',
            'status' => CampaignStatus::DRAFT->value,
        ]);

        $response = $this->post("/campaigns/{$campaign->id}/send_all");

        $response->assertOk();
        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'status' => CampaignStatus::SENDING->value,
        ]);

        $this->assertNotNull(Campaign::find($campaign->id)->scheduled_at);

        Queue::assertPushed(DispatchCampaignJob::class, function ($job) use ($campaign) {
            return $job->campaignId === $campaign->id;
        });
    }

    #[Test]
    public function can_queue_test_send_to_single_email(): void
    {
        $this->actingAsAdmin();
        Queue::fake();

        $campaign = Campaign::create([
            'name' => 'Test Campaign',
            'subject' => 'Hello',
            'content' => '<p>World</p>',
            'status' => CampaignStatus::DRAFT->value,
        ]);

        $email = 'test@example.com';

        $response = $this->post("/campaigns/{$campaign->id}/test_send", [
            'email' => $email,
            'name' => 'Tester',
        ]);

        $response->assertOk();

        $subscriber = Subscriber::where('email', $email)->first();
        $this->assertNotNull($subscriber);
        // Status cast may be an Enum in model, normalize assertion
        $this->assertSame(SubscriberStatus::Subscribed->value, $subscriber->status->value ?? (string) $subscriber->status);

        $this->assertDatabaseHas('campaign_receipts', [
            'campaign_id' => $campaign->id,
            'subscriber_id' => $subscriber->id,
            'status' => 'queued',
        ]);

        Queue::assertPushed(SendCampaignEmailJob::class, function ($job) use ($campaign, $subscriber) {
            return $job->campaignId === $campaign->id && $job->subscriberId === $subscriber->id;
        });
    }

    #[Test]
    public function can_view_campaign_detail_with_statistics(): void
    {
        $this->actingAsAdmin();

        $campaign = Campaign::create([
            'name' => 'Stats Campaign',
            'subject' => 'Stats',
            'content' => '<p>Stats</p>',
            'status' => CampaignStatus::DRAFT->value,
        ]);

        $subscriber = Subscriber::create([
            'email' => 'a@example.com',
            'name' => 'A',
            'token' => 'tok',
            'status' => SubscriberStatus::Subscribed->value,
            'subscribed_at' => now(),
        ]);

        CampaignReceipt::create([
            'campaign_id' => $campaign->id,
            'subscriber_id' => $subscriber->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->get("/campaigns/{$campaign->id}");

        $response->assertOk();
        // Basic assertions to ensure detail page renders and includes status label
        $response->assertSee((string) $campaign->id);
        $response->assertSee('Sent');
    }
}
