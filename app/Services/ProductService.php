<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function getProducts(Request $request, int $size = 20): LengthAwarePaginator
    {
        $page = $request->get('page', 1);

        $products = Product::with(['media', 'category', 'brand'])->published();

        $products->when(
            $request->has('category_id'),
            fn($q) => $q->where('product_category_id', $request->get('category_id'))
        );
        $products->when(
            $request->has('brand_id'),
            fn($q) => $q->where('product_brand_id', $request->get('brand_id'))
        );
        $products->when(
            $request->has('sex'),
            fn($q) => $q->where('sex', $request->get('sex'))
        );

        $products->when(
            $request->has('min_price') && $request->has('max_price'),
            fn($q) => $q->whereBetween('price', [...$request->only('min_price', 'max_price')])
        );

        $products->when($request->has('search'), function ($q) use ($request) {
            $searchTerm = $request->get('search');

            $q->where('name', 'like', "%{$searchTerm}%")
                ->orWhereHas('category', fn($q) => $q->where('name', 'like', "%{$searchTerm}%"))
                ->orWhereHas('brand', fn($q) => $q->where('name', 'like', "%{$searchTerm}%"));
        });

        $products->when(
            $request->has('sort_by'),
            function ($q) use ($request) {
                $sorts = [
                    'newest' => ['id', 'desc'],
                    'oldest' => ['id', 'asc'],
                    'sales' => ['sold_count', 'desc'],
                    'expensive' => ['price', 'desc'],
                    'cheapest' => ['price', 'asc'],
                ];

                $q->orderBy(...$sorts[$request->get('sort_by')] ?? $sorts['newest']);
            },
            fn($q) => $q->orderBy('id', 'desc')
        );

        $products = $products->paginate(perPage: $size, page: $page);

        return $products;
    }

    public function getSimilarProducts(Product $product, int $size = 5): Collection
    {
        throw_if(!$product->is_publish, ModelNotFoundException::class);

        return Product::with(['media', 'category', 'brand'])
            ->published()
            ->where('id', '!=', $product->id)
            ->where('name', 'like', "%{$product->name}%")
            ->latest('id')
            ->take($size)
            ->get();
    }
}
