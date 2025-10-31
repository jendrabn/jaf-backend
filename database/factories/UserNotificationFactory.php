<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserNotification>
 */
class UserNotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(2),
            'category' => fake()->randomElement(['transaction', 'account', 'promo', 'system']),
            'level' => fake()->randomElement(['info', 'warning', 'success', 'error']),
            'url' => fake()->optional(0.7)->url(),
            'icon' => fake()->optional(0.8)->randomElement(['fas fa-bell', 'fas fa-check', 'fas fa-exclamation', 'fas fa-info', 'fas fa-gift']),
            'meta' => fake()->optional(0.6)->randomElement([
                ['order_id' => fake()->numberBetween(1000, 9999)],
                ['promo_code' => fake()->word()],
                ['amount' => fake()->numberBetween(10000, 1000000)],
                ['action' => fake()->word()],
            ]),
            'read_at' => fake()->optional(0.3)->dateTime(),
        ];
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => fake()->dateTime(),
        ]);
    }

    /**
     * Create a transaction notification.
     */
    public function transaction(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'transaction',
            'title' => fake()->randomElement(['Order Confirmed', 'Payment Received', 'Order Shipped', 'Order Delivered']),
            'icon' => 'fas fa-shopping-cart',
        ]);
    }

    /**
     * Create an account notification.
     */
    public function account(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'account',
            'title' => fake()->randomElement(['Profile Updated', 'Password Changed', 'Login Alert']),
            'icon' => 'fas fa-user',
        ]);
    }

    /**
     * Create a promo notification.
     */
    public function promo(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'promo',
            'title' => fake()->randomElement(['Special Offer', 'Discount Available', 'New Promotion']),
            'icon' => 'fas fa-gift',
        ]);
    }

    /**
     * Create a system notification.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'system',
            'title' => fake()->randomElement(['System Maintenance', 'New Feature', 'System Update']),
            'icon' => 'fas fa-cog',
        ]);
    }
}
