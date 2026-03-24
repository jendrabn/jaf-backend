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

class BlogDetailGetTest extends ApiTestCase
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
    public function can_get_blog_detail_and_increment_views_count()
    {
        $tag = BlogTag::factory()->create();
        $blog = Blog::factory()->create([
            'title' => 'Blog detail',
            'slug' => 'blog-detail',
            'blog_category_id' => BlogCategory::factory()->create()->id,
            'user_id' => $this->createAdminUser()->id,
            'views_count' => 7,
            'is_publish' => true,
        ]);
        $blog->tags()->sync([$tag->id]);

        $response = $this->getJson('/api/blogs/'.$blog->slug);

        $response->assertOk()
            ->assertJsonPath('data.id', $blog->id)
            ->assertJsonPath('data.slug', $blog->slug)
            ->assertJsonPath('data.views_count', 8)
            ->assertJsonPath('data.author', $blog->author->name)
            ->assertJsonCount(1, 'data.tags');
    }
}
