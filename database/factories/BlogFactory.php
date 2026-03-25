<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blog>
 */
class BlogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $blog = FactoryData::blog();
        $category = BlogCategory::factory();

        return array_merge($blog, [
            'blog_category_id' => $category,
            'user_id' => User::factory(),
        ]);
    }

    // public function configure()
    // {
    //     return $this->afterCreating(function (Blog $blog) {
    //         $blog->tags()->attach(BlogTag::inRandomOrder()->take(3)->pluck('id'));

    //         $blog->addMediaFromUrl('https://picsum.photos/640/480')->toMediaCollection(Blog::MEDIA_COLLECTION_NAME);
    //     });
    // }
}
