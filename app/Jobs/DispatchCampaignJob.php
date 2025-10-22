<?php

namespace App\Jobs;

use App\Enums\CampaignReceiptStatus;
use App\Enums\SubscriberStatus;
use App\Models\Campaign;
use App\Models\CampaignReceipt;
use App\Models\Subscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class DispatchCampaignJob implements ShouldQueue
{
    use Queueable;

    /**
     * Dispatcher to enqueue per-subscriber SendCampaignEmailJob jobs.
     */
    public function __construct(public int $campaignId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $campaign = Campaign::query()->find($this->campaignId);

        if (! $campaign) {
            Log::warning('DispatchCampaignJob: Campaign not found', [
                'campaignId' => $this->campaignId,
            ]);

            return;
        }

        // Only target SUBSCRIBED subscribers
        Subscriber::query()
            ->where('status', SubscriberStatus::Subscribed->value)
            ->select(['id', 'email'])
            ->orderBy('id')
            ->chunk(500, function ($subscribers) use ($campaign) {
                foreach ($subscribers as $subscriber) {
                    // Ensure a receipt entry exists and is queued
                    CampaignReceipt::query()->firstOrCreate(
                        [
                            'campaign_id' => $campaign->id,
                            'subscriber_id' => $subscriber->id,
                        ],
                        [
                            'status' => CampaignReceiptStatus::Queued,
                        ]
                    );

                    // Dispatch a job per subscriber
                    SendCampaignEmailJob::dispatch($campaign->id, $subscriber->id);
                }
            });

        // Mark campaign as SENT timestamp. Status will be updated after emails are dispatched in practice.
        $campaign->update([
            'sent_at' => now(),
        ]);
    }
}
