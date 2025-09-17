<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
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
        return [
            'title' => fake()->sentence(4),
            'slug' => fake()->unique()->slug(),
            'content' => fake()->text(1000),
            'featured_image_description' => fake()->sentence(3),
            'min_read' => fake()->numberBetween(1, 100),
            'is_publish' => fake()->boolean(80),
            'blog_category_id' => BlogCategory::inRandomOrder()->first()->id,
            'user_id' => User::role(User::ROLE_ADMIN)->inRandomOrder()->first()->id
        ];
    }

    // public function configure()
    // {
    //     return $this->afterCreating(function (Blog $blog) {
    //         $blog->tags()->attach(BlogTag::inRandomOrder()->take(3)->pluck('id'));

    //         $blog->addMediaFromUrl('https://picsum.photos/640/480')->toMediaCollection(Blog::MEDIA_COLLECTION_NAME);
    //     });
    // }
}
