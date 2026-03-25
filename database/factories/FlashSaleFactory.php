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
        return FactoryData::flashSale();
    }

    public function running(): self
    {
        $start = now()->subHour();

        return $this->state(fn () => [
            'name' => 'Running Perfume Flash Sale',
            'start_at' => $start,
            'end_at' => now()->addHours(3),
            'is_active' => true,
        ]);
    }

    public function finished(): self
    {
        return $this->state(fn () => [
            'name' => 'Finished Perfume Flash Sale',
            'start_at' => now()->subDays(1),
            'end_at' => now()->subHours(6),
            'is_active' => false,
        ]);
    }
}
