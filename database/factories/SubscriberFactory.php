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
        return [
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'token' => fake()->unique()->sha256(),
            'status' => fake()->randomElement(SubscriberStatus::cases()),
            'subscribed_at' => fake()->optional(0.7)->dateTimeBetween('-1 year', 'now'),
            'unsubscribed_at' => fake()->optional(0.2)->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
