<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class BlogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content_thumbnail' => Str::limit(strip_tags($this->content), 200),
            'min_read' => $this->min_read,
            'featured_image' => $this->featured_image->url ?? asset('images/default-blog.png'),
            'views_count' => $this->views_count,
            'author' => $this->author?->name,
            'category' => BlogCategoryResource::make($this->category),
            'created_at' => $this->created_at,
        ];
    }
}
