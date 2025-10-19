<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Http\Resources\BlogResource;
use App\Http\Resources\ProductResource;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LandingController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $banners = Banner::with(['media'])
            ->take(10)
            ->get();

        $products = Product::with(['media', 'category', 'brand'])
            ->published()
            ->latest('id')
            ->take(10)
            ->get();

        $blogs = Blog::with(['category', 'tags'])
            ->latest('id')
            ->take(10)
            ->get();

        return response()->json([
            'data' => [
                'banners' => BannerResource::collection($banners),
                'products' => ProductResource::collection($products),
                'blogs' => BlogResource::collection($blogs),
            ],
        ], Response::HTTP_OK);
    }
}
