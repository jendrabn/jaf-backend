<?php

namespace Database\Factories;

use App\Enums\CampaignReceiptStatus;
use App\Models\Campaign;
use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampaignReceipt>
 */
class CampaignReceiptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(CampaignReceiptStatus::cases());
        $sentAt = null;
        $openedAt = null;
        $clickedAt = null;

        if ($status !== CampaignReceiptStatus::Queued) {
            $sentAt = fake()->dateTimeBetween('-14 days', 'now');
        }

        if (in_array($status, [CampaignReceiptStatus::Opened, CampaignReceiptStatus::Clicked], true) && $sentAt !== null) {
            $openedAt = fake()->dateTimeBetween($sentAt, 'now');
        }

        if ($status === CampaignReceiptStatus::Clicked && $openedAt !== null) {
            $clickedAt = fake()->dateTimeBetween($openedAt, 'now');
        }

        return [
            'campaign_id' => Campaign::factory(),
            'subscriber_id' => Subscriber::factory(),
            'status' => $status,
            'sent_at' => $sentAt,
            'opened_at' => $openedAt,
            'clicked_at' => $clickedAt,
        ];
    }
}
