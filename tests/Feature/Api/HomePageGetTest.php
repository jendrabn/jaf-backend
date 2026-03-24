<?php

namespace Tests\Feature\Api;

use App\Models\Banner;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\ApiTestCase;

class HomePageGetTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_get_banners_and_latest_products()
    {
        $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);

        Role::firstOrCreate(['name' => User::ROLE_ADMIN]);
        $admin = User::factory()->create();
        $admin->assignRole(User::ROLE_ADMIN);

        $banners = Banner::factory(12)->create();

        // Add image only for first banner
        $banners[0]->addMedia(UploadedFile::fake()->image('banner.jpg'))->toMediaCollection(Banner::MEDIA_COLLECTION_NAME);

        $this->createProduct(['is_publish' => false]);
        $products = $this->createProduct(count: 12);
        Blog::factory(3)->create([
            'blog_category_id' => BlogCategory::factory()->create()->id,
            'user_id' => $admin->id,
            'is_publish' => true,
        ]);

        $response = $this->getJson('/api/landing');

        $response
            ->assertOk()
            ->assertJsonCount(10, 'data.banners')
            ->assertJsonCount(10, 'data.products')
            ->assertJsonCount(3, 'data.blogs');

        $this->assertStringStartsWith('http', $response['data']['banners'][0]['image']);
        $this->assertSame($products->sortByDesc('id')->first()->id, $response['data']['products'][0]['id']);
    }
}
