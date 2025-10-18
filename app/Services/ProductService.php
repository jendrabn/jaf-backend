<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function getProducts(Request $request, int $size = 20): LengthAwarePaginator
    {
        $page = $request->get('page', 1);

        $products = Product::with(['media', 'category', 'brand', 'orderItems', 'orderItems.rating'])->published();

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

            if (! empty($searchTerm)) {
                $q->where(function ($query) use ($searchTerm) {
                    $query->whereRaw('MATCH(name) AGAINST(? IN BOOLEAN MODE)', [$searchTerm])
                        ->orWhere('name', 'like', "%{$searchTerm}%")
                        ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$searchTerm}%"))
                        ->orWhereHas('brand', fn($b) => $b->where('name', 'like', "%{$searchTerm}%"));
                });
            }
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
        throw_if(! $product->is_publish, ModelNotFoundException::class);

        $keywords = array_filter(explode(' ', $product->name));

        return Product::with(['media', 'category', 'brand'])
            ->published()
            ->where('id', '!=', $product->id)
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('name', 'like', "%{$keyword}%");
                }
            })
            ->latest('id')
            ->take($size)
            ->get();
    }

    /**
     * Get auto-complete search suggestions for product names.
     *
     * Builds suggestions from matching product names, single-word tokens, and bigrams
     * that start with the given prefix (case-insensitive). Results are ranked by
     * frequency and relevance and limited to the requested size.
     *
     * @return array<string>
     */
    public function getProductSuggestions(string $searchTerm, int $size = 10): array
    {
        $searchTerm = trim($searchTerm);

        if ($searchTerm === '') {
            return [];
        }

        $prefix = mb_strtolower($searchTerm);
        $driver = DB::connection()->getDriverName();

        $query = Product::query()
            ->published()
            ->select(['name']);

        if ($driver === 'mysql') {
            // Use FULLTEXT with BOOLEAN MODE for prefix search (q*).
            // Requires fulltext index on products.name.
            $against = $prefix . '*';
            $query->whereRaw('MATCH(name) AGAINST(? IN BOOLEAN MODE)', [$against]);
        } else {
            // Fallback for drivers without FULLTEXT (e.g., sqlite in tests).
            $query->where('name', 'like', "%{$searchTerm}%");
        }

        $products = $query
            ->limit(100)
            ->get();

        $suggestions = [];   // normalized key => original suggestion
        $counts = [];        // normalized key => score

        foreach ($products as $product) {
            $name = $product->name;
            $lowerName = mb_strtolower($name);

            // Full product name starts with the prefix -> strong signal
            if (mb_stripos($lowerName, $prefix) === 0) {
                $key = $lowerName;
                $suggestions[$key] = $name;
                $counts[$key] = ($counts[$key] ?? 0) + 3;
            }

            // Tokenize by spaces and punctuation, then capture:
            // - single tokens starting with prefix
            // - bigrams starting with a token that starts with prefix
            $tokens = preg_split('/[\\s\\p{P}]+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            for ($i = 0, $n = count($tokens); $i < $n; $i++) {
                $token = $tokens[$i];
                $lowToken = mb_strtolower($token);

                if (mb_strpos($lowToken, $prefix) === 0) {
                    // Single-word suggestion
                    $key = $lowToken;
                    $suggestions[$key] = $token;
                    $counts[$key] = ($counts[$key] ?? 0) + 2;

                    // Two-word phrase suggestion (bigram)
                    if ($i + 1 < $n) {
                        $bigram = $token . ' ' . $tokens[$i + 1];
                        $key2 = mb_strtolower($bigram);
                        $suggestions[$key2] = $bigram;
                        $counts[$key2] = ($counts[$key2] ?? 0) + 1;
                    }
                }
            }
        }

        // Sort suggestions by score desc, then alphabetically
        uasort($suggestions, function (string $a, string $b) use ($counts): int {
            $ka = mb_strtolower($a);
            $kb = mb_strtolower($b);
            $ca = $counts[$ka] ?? 0;
            $cb = $counts[$kb] ?? 0;

            if ($ca === $cb) {
                return $a <=> $b;
            }

            return $cb <=> $ca;
        });

        return array_slice(array_values($suggestions), 0, $size);
    }
}
