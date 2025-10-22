<?php

namespace Tests\Feature\Admin;

use App\Enums\CampaignStatus;
use App\Jobs\DispatchCampaignJob;
use App\Jobs\SendCampaignEmailJob;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CampaignNewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_renders_datatable_view(): void
    {
        $this->withoutMiddleware();

        $response = $this->get(route('admin.campaigns.index'));

        $response->assertStatus(200);
        $response->assertSee('Campaigns');
    }

    public function test_admin_can_create_campaign_and_redirect_to_edit(): void
    {
        $this->withoutMiddleware();

        $payload = [
            'name' => 'October Promo',
            'subject' => 'Big Sale',
            'content' => '<p>Hello Subscribers!</p>',
            'scheduled_at' => now()->addHour()->format('Y-m-d H:i:s'),
            'status' => 'draft',
        ];

        $response = $this->post(route('admin.campaigns.store'), $payload);

        $response->assertRedirect();
        $campaign = Campaign::query()->firstOrFail();
        $this->assertSame('October Promo', $campaign->name);
        $this->assertSame('Big Sale', $campaign->subject);
        $this->assertSame('<p>Hello Subscribers!</p>', $campaign->content);
        $this->assertTrue($campaign->status === CampaignStatus::DRAFT);
    }

    public function test_send_all_queues_dispatch_job_and_updates_status(): void
    {
        $this->withoutMiddleware();

        $campaign = Campaign::query()->create([
            'name' => 'Holiday Campaign',
            'subject' => 'Happy Holidays',
            'content' => '<p>Warm wishes!</p>',
            'status' => CampaignStatus::DRAFT,
        ]);

        Queue::fake();

        $response = $this->post(route('admin.campaigns.send_all', $campaign));

        $response->assertStatus(200);
        Queue::assertPushed(DispatchCampaignJob::class, function (DispatchCampaignJob $job) use ($campaign) {
            return $job->campaignId === $campaign->id;
        });

        $campaign->refresh();
        $this->assertTrue($campaign->status === CampaignStatus::SENDING);
        $this->assertNotNull($campaign->scheduled_at);
    }

    public function test_test_send_queues_single_send_job(): void
    {
        $this->withoutMiddleware();

        $campaign = Campaign::query()->create([
            'name' => 'Test Campaign',
            'subject' => 'Test Subject',
            'content' => '<p>Test Content</p>',
            'status' => CampaignStatus::DRAFT,
        ]);

        Queue::fake();

        $response = $this->post(route('admin.campaigns.test_send', $campaign), [
            'email' => 'tester@example.com',
            'name' => 'Tester',
        ]);

        $response->assertStatus(200);

        Queue::assertPushed(SendCampaignEmailJob::class, function (SendCampaignEmailJob $job) use ($campaign) {
            return $job->campaignId === $campaign->id;
        });
    }
}
