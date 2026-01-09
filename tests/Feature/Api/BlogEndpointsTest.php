<?php

namespace Tests\Feature\Api;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\ApiTestCase;

class BlogEndpointsTest extends ApiTestCase
{
    use RefreshDatabase;

    private ?User $adminUser = null;

    private function createAdminUser(): User
    {
        Role::firstOrCreate(['name' => User::ROLE_ADMIN]);

        if ($this->adminUser) {
            return $this->adminUser;
        }

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole(User::ROLE_ADMIN);

        return $this->adminUser;
    }

    private function createBlog(array $attributes = []): Blog
    {
        $categoryId = $attributes['blog_category_id'] ?? BlogCategory::factory()->create()->id;

        return Blog::factory()->create(array_merge([
            'is_publish' => true,
            'blog_category_id' => $categoryId,
            'user_id' => $this->createAdminUser()->id,
        ], $attributes));
    }

    #[Test]
    public function latest_endpoint_returns_most_recent_blogs_with_default_limit()
    {
        $category = BlogCategory::factory()->create();
        $now = Carbon::now();
        $latestBlog = $this->createBlog([
            'slug' => 'latest-blog',
            'blog_category_id' => $category->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach (range(1, 4) as $index) {
            $this->createBlog([
                'blog_category_id' => $category->id,
                'created_at' => $now->copy()->subMinutes($index),
                'updated_at' => $now->copy()->subMinutes($index),
            ]);
        }

        $response = $this->getJson('/api/blogs/latest');

        $response->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('data.0.slug', $latestBlog->slug);
    }

    #[Test]
    public function latest_endpoint_caps_limit_at_twenty()
    {
        $category = BlogCategory::factory()->create();
        $now = Carbon::now();

        foreach (range(1, 22) as $index) {
            $this->createBlog([
                'blog_category_id' => $category->id,
                'created_at' => $now->copy()->subMinutes($index),
                'updated_at' => $now->copy()->subMinutes($index),
            ]);
        }

        $response = $this->getJson('/api/blogs/latest?limit=100');

        $response->assertOk()
            ->assertJsonCount(20, 'data');
    }

    #[Test]
    public function popular_endpoint_filters_by_window_and_orders_by_views_count()
    {
        $category = BlogCategory::factory()->create();
        $now = Carbon::now();

        $recentBlog = $this->createBlog([
            'slug' => 'recent-blog',
            'views_count' => 120,
            'blog_category_id' => $category->id,
            'created_at' => $now->copy()->subHours(1),
            'updated_at' => $now->copy()->subHours(1),
        ]);

        $twoDaysAgo = $this->createBlog([
            'slug' => 'two-days-ago',
            'views_count' => 200,
            'blog_category_id' => $category->id,
            'created_at' => $now->copy()->subDays(2),
            'updated_at' => $now->copy()->subDays(2),
        ]);

        $this->createBlog([
            'views_count' => 300,
            'blog_category_id' => $category->id,
            'created_at' => $now->copy()->subDays(10),
            'updated_at' => $now->copy()->subDays(10),
        ]);

        $response = $this->getJson('/api/blogs/popular');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', $twoDaysAgo->slug);

        $response = $this->getJson('/api/blogs/popular?window=1d');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $recentBlog->slug);
    }

    #[Test]
    public function related_endpoint_returns_related_blogs_and_respects_limits()
    {
        $category = BlogCategory::factory()->create();
        $otherCategory = BlogCategory::factory()->create();
        $sharedTag = BlogTag::factory()->create();
        $otherTag = BlogTag::factory()->create();
        $now = Carbon::now();

        $baseBlog = $this->createBlog([
            'slug' => 'base-blog',
            'blog_category_id' => $category->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $baseBlog->tags()->sync([$sharedTag->id]);

        foreach (range(1, 5) as $index) {
            $this->createBlog([
                'blog_category_id' => $category->id,
                'created_at' => $now->copy()->subMinutes($index),
                'updated_at' => $now->copy()->subMinutes($index),
            ]);
        }

        $tagRelatedBlog = $this->createBlog([
            'blog_category_id' => $otherCategory->id,
            'created_at' => $now->copy()->subMinutes(6),
            'updated_at' => $now->copy()->subMinutes(6),
        ]);

        $tagRelatedBlog->tags()->sync([$sharedTag->id]);

        $this->createBlog([
            'blog_category_id' => $otherCategory->id,
            'created_at' => $now->copy()->subMinutes(7),
            'updated_at' => $now->copy()->subMinutes(7),
        ])->tags()->sync([$otherTag->id]);

        $response = $this->getJson('/api/blogs/base-blog/related');

        $response->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonFragment(['slug' => $tagRelatedBlog->slug])
            ->assertJsonMissing(['slug' => $baseBlog->slug]);

        $response = $this->getJson('/api/blogs/base-blog/related?limit=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
