<?php

namespace Database\Factories;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tax>
 */
class TaxFactory extends Factory
{
    protected $model = Tax::class;

    public function definition(): array
    {
        $tax = FactoryData::tax();

        return [
            'name' => $tax['name'],
            'rate' => $tax['rate'],
        ];
    }
}
