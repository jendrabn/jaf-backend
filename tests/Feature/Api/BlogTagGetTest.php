<?php

namespace Tests\Feature\Api;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\ApiTestCase;

class BlogTagGetTest extends ApiTestCase
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
    public function can_get_blog_tags_with_blog_counts()
    {
        $adminUser = $this->createAdminUser();
        $category = BlogCategory::factory()->create();
        $tag = BlogTag::factory()->create([
            'name' => 'Featured',
            'slug' => 'featured',
        ]);

        $blog = Blog::factory()->create([
            'blog_category_id' => $category->id,
            'user_id' => $adminUser->id,
            'is_publish' => true,
        ]);
        $blog->tags()->sync([$tag->id]);

        $response = $this->getJson('/api/blogs/tags');

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $tag->id,
                'name' => 'Featured',
                'slug' => 'featured',
                'blogs_count' => 1,
            ]);
    }
}
