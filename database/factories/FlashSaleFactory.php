<?php

namespace Database\Factories;

use App\Models\FlashSale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FlashSale>
 */
class FlashSaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+2 weeks');
        $end = (clone $start)->modify('+'.fake()->numberBetween(1, 12).' hours');

        return [
            'name' => 'Flash Sale '.fake()->numerify('##.##'),
            'description' => fake()->sentence(),
            'start_at' => $start,
            'end_at' => $end,
            'is_active' => true,
        ];
    }

    public function running(): self
    {
        $start = now()->subHour();

        return $this->state(fn () => [
            'start_at' => $start,
            'end_at' => now()->addHours(3),
        ]);
    }

    public function finished(): self
    {
        return $this->state(fn () => [
            'start_at' => now()->subDays(1),
            'end_at' => now()->subHours(6),
            'is_active' => false,
        ]);
    }
}
