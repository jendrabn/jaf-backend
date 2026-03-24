<?php

namespace Tests\Feature\Api;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\ApiTestCase;

class BlogCategoryGetTest extends ApiTestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        Role::firstOrCreate(['name' => User::ROLE_ADMIN]);

        $user = User::factory()->create();
        $user->assignRole(User::ROLE_ADMIN);

        return $user;
    }

    #[Test]
    public function can_get_blog_categories_with_blog_counts()
    {
        $adminUser = $this->createAdminUser();
        $category = BlogCategory::factory()->create([
            'name' => 'News',
            'slug' => 'news',
        ]);

        Blog::factory()->count(2)->create([
            'blog_category_id' => $category->id,
            'user_id' => $adminUser->id,
            'is_publish' => true,
        ]);

        $response = $this->getJson('/api/blogs/categories');

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $category->id,
                'name' => 'News',
                'slug' => 'news',
                'blogs_count' => 2,
            ]);
    }
}
