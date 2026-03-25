<?php

// database/factories/ProductFactory.php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

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
        $product = FactoryData::product();
        $category = ProductCategory::query()->firstOrCreate(
            ['slug' => $product['category_slug']],
            [
                'name' => $product['category_name'],
                'slug' => $product['category_slug'],
            ]
        );
        $brand = ProductBrand::query()->firstOrCreate(
            ['slug' => $product['brand_slug']],
            [
                'name' => $product['brand_name'],
                'slug' => $product['brand_slug'],
            ]
        );

        return [
            'product_category_id' => $category->id,
            'product_brand_id' => $brand->id,
            'name' => $product['name'],
            'slug' => $product['slug'],
            'weight' => $product['weight'],
            'price' => $product['price'],
            'stock' => $product['stock'],
            'description' => $product['description'],
            'is_publish' => true,
            'sex' => $product['sex'],
        ];
    }

    public function hasImages(int $count = 1): static
    {
        return $this->afterCreating(
            function (Product $product) use ($count): void {
                for ($i = 0; $i < $count; $i++) {
                    $product
                        ->addMedia(UploadedFile::fake()->image(sprintf('%s-%02d.jpg', $product->slug, $i + 1)))
                        ->toMediaCollection(Product::MEDIA_COLLECTION_NAME);
                }
            }
        );
    }
}
