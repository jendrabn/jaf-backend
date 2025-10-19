<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogDetailResource extends JsonResource
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
            'content' => $this->content,
            'min_read' => $this->min_read,
            'featured_image' => $this->featured_image?->url,
            'featured_image_description' => $this->featured_image_description,
            'views_count' => $this->views_count,
            'author' => $this->author?->name,
            'category' => BlogCategoryResource::make($this->category),
            'tags' => BlogTagResource::collection($this->tags),
            'created_at' => $this->created_at,
        ];
    }
}
