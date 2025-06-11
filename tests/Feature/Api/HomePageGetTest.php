<?php

namespace Tests\Feature\Api;

use App\Models\Banner;
use Database\Seeders\{ProductBrandSeeder, ProductCategorySeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;

class HomePageGetTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_get_banners_and_latest_products()
    {
        $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);

        $banners = Banner::factory(12)->create();

        // Add image only for first banner
        $banners[0]->addMedia(UploadedFile::fake()->image('banner.jpg'))->toMediaCollection(Banner::MEDIA_COLLECTION_NAME);

        $this->createProduct(['is_publish' => false]);
        $products = $this->createProduct(count: 12);

        $expectedBanners = $banners->sortBy('id')->take(10);
        $expectedProducts = $products->sortByDesc('id')->take(10);

        $response = $this->getJson('/api/landing');

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'banners' =>
                        $expectedBanners->map(fn($banner) => [
                            'id' => $banner->id,
                            'image' => $banner->image ? $banner->image->getUrl() : null,
                            'image_description' => $banner->image_description,
                            'url' => $banner->url
                        ])->toArray(),
                    'products' => $this->formatProductData($expectedProducts)
                ]
            ]);

        $this->assertStringStartsWith('http', $response['data']['banners'][0]['image']);
    }
}
