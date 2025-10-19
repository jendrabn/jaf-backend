<?php

namespace App\Services;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogService
{
    public function getBlogs(Request $request, int $size = 10)
    {
        $page = $request->get('page', 1);

        $blogs = Blog::with(['media', 'category', 'tags'])->published();

        $blogs->when(
            $request->has('category_id'),
            fn ($q) => $q->where('blog_category_id', $request->get('category_id'))
        );

        $blogs->when(
            $request->has('tag_id'),
            fn ($q) => $q->whereHas('tags', fn ($q) => $q->where('blog_tag_id', $request->get('tag_id')))
        );

        $blogs->when(
            $request->has('search'),
            fn ($q) => $q->where('title', 'like', "%{$request->get('search')}%")
                ->orWhereHas('category', fn ($q) => $q->where('name', 'like', "%{$request->get('search')}%"))
                ->orWhereHas('tags', fn ($q) => $q->where('name', 'like', "%{$request->get('search')}%"))
        );

        $blogs->when(
            $request->has('sort_by'),
            function ($q) use ($request) {
                $sorts = [
                    'newest' => ['id', 'desc'],
                    'oldest' => ['id', 'asc'],
                    'views' => ['views_count', 'desc'],
                ];

                return $q->orderBy(...$sorts[$request->get('sort_by') ?? $sorts['newest']]);
            },
            fn ($q) => $q->orderBy('id', 'desc')
        );

        $blogs = $blogs->paginate(perPage: $size, page: $page);

        return $blogs;
    }
}
