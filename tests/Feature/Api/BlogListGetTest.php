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

class BlogListGetTest extends ApiTestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        Role::firstOrCreate(['name' => User::ROLE_ADMIN]);

        $user = User::factory()->create();
        $user->assignRole(User::ROLE_ADMIN);

        return $user;
    }

    private function createBlog(array $attributes = []): Blog
    {
        return Blog::factory()->create(array_merge([
            'is_publish' => true,
            'blog_category_id' => BlogCategory::factory()->create()->id,
            'user_id' => $this->createAdminUser()->id,
        ], $attributes));
    }

    #[Test]
    public function can_get_published_blogs_filtered_by_category()
    {
        $category = BlogCategory::factory()->create();
        $otherCategory = BlogCategory::factory()->create();

        $matchingBlog = $this->createBlog([
            'title' => 'Filtered blog',
            'slug' => 'filtered-blog',
            'blog_category_id' => $category->id,
        ]);

        $this->createBlog([
            'title' => 'Other blog',
            'slug' => 'other-blog',
            'blog_category_id' => $otherCategory->id,
        ]);
        $this->createBlog([
            'title' => 'Draft blog',
            'slug' => 'draft-blog',
            'blog_category_id' => $category->id,
            'is_publish' => false,
        ]);

        $response = $this->getJson('/api/blogs?category_id='.$category->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingBlog->id)
            ->assertJsonPath('data.0.slug', $matchingBlog->slug)
            ->assertJsonPath('page.total', 1);
    }

    #[Test]
    public function can_get_published_blogs_filtered_by_tag()
    {
        $tag = BlogTag::factory()->create();
        $otherTag = BlogTag::factory()->create();

        $matchingBlog = $this->createBlog([
            'title' => 'Tagged blog',
            'slug' => 'tagged-blog',
        ]);
        $matchingBlog->tags()->sync([$tag->id]);

        $otherBlog = $this->createBlog([
            'title' => 'Other tag blog',
            'slug' => 'other-tag-blog',
        ]);
        $otherBlog->tags()->sync([$otherTag->id]);

        $response = $this->getJson('/api/blogs?tag_id='.$tag->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingBlog->id)
            ->assertJsonPath('data.0.slug', $matchingBlog->slug);
    }
}
