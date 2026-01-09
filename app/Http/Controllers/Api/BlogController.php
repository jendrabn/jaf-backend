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
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends Controller
{
    private const DEFAULT_LIMIT = 4;
    private const MAX_LIMIT = 20;
    private const POPULAR_WINDOWS = [
        '1d' => 1,
        '7d' => 7,
        '30d' => 30,
    ];

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

    public function get(Blog $blog)
    {
        $blog->increment('views_count');

        return BlogDetailResource::make($blog)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function related(Request $request, Blog $blog)
    {
        $limit = $this->resolveLimit($request->get('limit'));

        $relatedTagIds = $blog->tags()
            ->pluck('blog_tag_id')
            ->all();

        $related = Blog::with(['media', 'category', 'tags', 'author'])
            ->published()
            ->where('id', '!=', $blog->id)
            ->where(function ($query) use ($blog, $relatedTagIds) {
                $query->where('blog_category_id', $blog->blog_category_id);

                if (! empty($relatedTagIds)) {
                    $query->orWhereHas('tags', fn ($nodeQuery) => $nodeQuery->whereIn('blog_tag_id', $relatedTagIds));
                }
            })
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        return BlogCollection::make($related)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function popular(Request $request)
    {
        $limit = $this->resolveLimit($request->get('limit'));
        $windowStart = $this->popularityWindowStart($request->get('window'));

        $blogs = Blog::with(['media', 'category', 'tags', 'author'])
            ->published()
            ->where('created_at', '>=', $windowStart)
            ->orderBy('views_count', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        return BlogCollection::make($blogs)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function latest(Request $request)
    {
        $limit = $this->resolveLimit($request->get('limit'));

        $blogs = Blog::with(['media', 'category', 'tags', 'author'])
            ->published()
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        return BlogCollection::make($blogs)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    private function resolveLimit(mixed $limit): int
    {
        $size = is_numeric($limit) ? (int) $limit : 0;

        if ($size < 1) {
            return self::DEFAULT_LIMIT;
        }

        return min($size, self::MAX_LIMIT);
    }

    private function popularityWindowStart(?string $window): Carbon
    {
        $normalizedWindow = strtolower($window ?? '7d');

        if (! isset(self::POPULAR_WINDOWS[$normalizedWindow])) {
            $normalizedWindow = '7d';
        }

        return Carbon::now()->subDays(self::POPULAR_WINDOWS[$normalizedWindow]);
    }
}
