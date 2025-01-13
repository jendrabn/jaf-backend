<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\{
    ProductBrandResource,
    ProductCategoryResource,
    ProductCollection,
    ProductDetailResource
};
use App\Services\ProductService;
use App\Models\{
    Product,
    ProductBrand,
    ProductCategory
};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService)
    {
    }

    public function list(Request $request): JsonResponse
    {
        $products = $this->productService->getProducts($request);

        return ProductCollection::make($products)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function get(Product $product): JsonResponse
    {
        throw_if(!$product->is_publish, ModelNotFoundException::class);

        $product->load(['media', 'category', 'brand']);

        return ProductDetailResource::make($product)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function similars(Product $product): JsonResponse
    {
        $products = $this->productService->getSimilarProducts($product);

        return ProductCollection::make($products)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function categories(): JsonResponse
    {
        $categories = ProductCategory::withCount([
            'products' => function ($query) {
                $query->published();
            }
        ])
            ->get();

        return ProductCategoryResource::collection($categories)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function brands(): JsonResponse
    {
        $brands = ProductBrand::withCount([
            'products' => function ($query) {
                $query->published();
            }
        ])
            ->get();

        return ProductBrandResource::collection($brands)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
