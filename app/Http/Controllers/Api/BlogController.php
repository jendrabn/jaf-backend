<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogCategoryResource;
use App\Http\Resources\BlogCollection;
use App\Http\Resources\BlogDetailResource;
use App\Http\Resources\BlogTagResource;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Services\BlogService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends Controller
{
    public function __construct(private BlogService $service) {}

    public function categories()
    {
        $categories = BlogCategory::withCount('blogs')->get();

        return BlogCategoryResource::collection($categories)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function tags()
    {
        $tags = BlogTag::withCount('blogs')->get();

        return BlogTagResource::collection($tags)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function list(Request $request)
    {
        $blogs = $this->service->getBlogs($request);

        return BlogCollection::make($blogs)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function get(string $slug)
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();
        $blog->increment('views_count');

        return BlogDetailResource::make($blog)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
