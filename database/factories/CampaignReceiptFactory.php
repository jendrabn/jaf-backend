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
        return [
            'campaign_id' => Campaign::factory(),
            'subscriber_id' => Subscriber::factory(),
            'status' => fake()->randomElement(CampaignReceiptStatus::cases()),
            'sent_at' => fake()->optional(0.8)->dateTimeBetween('-1 month', 'now'),
            'opened_at' => fake()->optional(0.6)->dateTimeBetween('-2 weeks', 'now'),
            'clicked_at' => fake()->optional(0.3)->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
