<?php

// database/factories/ProductFactory.php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_category_id' => ProductCategory::inRandomOrder()->first()->id,
            'product_brand_id' => ProductBrand::inRandomOrder()->first()->id ?? null,
            'name' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'weight' => fake()->numberBetween(350, 1250),
            'price' => fake()->numberBetween(50000, 1000000),
            'stock' => fake()->numberBetween(100, 1000),
            'description' => fake()->paragraph(),
            'is_publish' => true,
            'sex' => fake()->randomElement([1, 2, 3]),
        ];
    }

    public function hasImages(int $count = 1)
    {
        return $this->afterCreating(
            function (Product $product) use ($count) {
                for ($i = 0; $i < $count; $i++) {
                    $product->addMediaFromUrl('https://picsum.photos/150')->toMediaCollection(Product::MEDIA_COLLECTION_NAME);
                }
            }
        );
    }
}
