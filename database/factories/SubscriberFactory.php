<?php

namespace Database\Factories;

use App\Enums\SubscriberStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscriber>
 */
class SubscriberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(SubscriberStatus::cases());
        $subscribedAt = $status !== SubscriberStatus::Pending
            ? fake()->dateTimeBetween('-1 year', '-1 day')
            : null;
        $unsubscribedAt = $status === SubscriberStatus::Unsubscribed
            ? fake()->dateTimeBetween($subscribedAt ?? '-30 days', 'now')
            : null;

        return [
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'token' => fake()->unique()->sha256(),
            'status' => $status,
            'subscribed_at' => $subscribedAt,
            'unsubscribed_at' => $unsubscribedAt,
        ];
    }
}
