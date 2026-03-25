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
        return array_merge(
            ['user_id' => User::factory()],
            FactoryData::notification()
        );
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
            'title' => fake()->randomElement(['Order parfum dikonfirmasi', 'Pembayaran berhasil diterima', 'Paket siap dikirim', 'Pesanan telah sampai']),
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
            'title' => fake()->randomElement(['Profil berhasil diperbarui', 'Password akun diubah', 'Login dari perangkat baru']),
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
            'title' => fake()->randomElement(['Promo parfum akhir pekan', 'Voucher koleksi premium tersedia', 'Diskon room spray baru aktif']),
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
            'title' => fake()->randomElement(['Maintenance sistem terjadwal', 'Fitur wishlist baru aktif', 'Pembaruan aplikasi tersedia']),
            'icon' => 'fas fa-cog',
        ]);
    }
}
