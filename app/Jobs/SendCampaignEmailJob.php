<?php

namespace App\Jobs;

use App\Enums\CampaignReceiptStatus;
use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\CampaignReceipt;
use App\Models\Subscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCampaignEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * The campaign and subscriber identifiers.
     */
    public function __construct(public int $campaignId, public int $subscriberId) {}

    /**
     * Execute the job: send campaign email to a subscriber and update receipt status.
     */
    public function handle(): void
    {
        $campaign = Campaign::query()->find($this->campaignId);
        $subscriber = Subscriber::query()->find($this->subscriberId);

        if (! $campaign || ! $subscriber) {
            Log::warning('SendCampaignEmailJob: Missing campaign or subscriber', [
                'campaignId' => $this->campaignId,
                'subscriberId' => $this->subscriberId,
            ]);

            return;
        }

        $receipt = CampaignReceipt::query()->firstOrCreate(
            [
                'campaign_id' => $campaign->id,
                'subscriber_id' => $subscriber->id,
            ],
            [
                'status' => CampaignReceiptStatus::Queued,
            ]
        );

        try {
            // Queue the email delivery; mailable implements ShouldQueue
            Mail::to($subscriber->email)->queue(new CampaignMail(
                subjectLine: $campaign->subject,
                html: $campaign->content,
                subscriber: $subscriber,
                receiptId: $receipt->id
            ));

            $receipt->update([
                'status' => CampaignReceiptStatus::Sent,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $receipt->update([
                'status' => CampaignReceiptStatus::Failed,
            ]);

            Log::error('SendCampaignEmailJob: Failed to send campaign', [
                'campaignId' => $campaign->id,
                'subscriberId' => $subscriber->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
