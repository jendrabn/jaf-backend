<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRatingRequest;
use App\Models\Product;
use App\Models\ProductRating;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductRatingController extends Controller
{
    public function destroy(Product $product, int $productRating): JsonResponse
    {
        $rating = ProductRating::findOrFail($productRating);
        $this->abortIfRatingDoesNotBelongToProduct($product, $rating);

        $snapshot = $rating->toArray();
        $ratingId = $rating->id;

        $rating->delete();

        audit_log(
            event: 'deleted',
            description: 'admin:delete_product_rating',
            before: $snapshot,
            after: null,
            extra: [
                'product_id' => $product->id,
            ],
            subjectId: $ratingId,
            subjectType: ProductRating::class
        );

        return response()->json(['message' => 'Product rating deleted successfully.'], Response::HTTP_OK);
    }

    public function massDestroy(ProductRatingRequest $request, Product $product): JsonResponse
    {
        $ids = $request->validated('ids');

        $ratings = ProductRating::query()
            ->whereIn('id', $ids)
            ->whereHas('orderItem', fn ($query) => $query->where('product_id', $product->id))
            ->pluck('id');

        if ($ratings->isEmpty()) {
            return response()->json(['message' => 'No product ratings found for deletion.'], Response::HTTP_OK);
        }

        ProductRating::whereIn('id', $ratings)->delete();

        $count = $ratings->count();

        audit_log(
            event: 'bulk_deleted',
            description: 'admin:bulk_delete_product_ratings',
            before: null,
            after: null,
            extra: [
                'product_id' => $product->id,
                'changed' => ['ids' => $ratings->toArray(), 'count' => $count],
                'properties' => ['count' => $count],
            ],
            subjectId: null,
            subjectType: ProductRating::class
        );

        return response()->json(['message' => 'Product ratings deleted successfully.'], Response::HTTP_OK);
    }

    public function publish(Product $product, int $productRating): JsonResponse
    {
        $rating = ProductRating::findOrFail($productRating);
        $this->abortIfRatingDoesNotBelongToProduct($product, $rating);

        $rating->update([
            'is_publish' => ! $rating->is_publish,
        ]);

        audit_log(
            event: 'updated',
            description: 'admin:update_product_rating_publish_status',
            before: null,
            after: [
                'is_publish' => $rating->is_publish,
            ],
            extra: [
                'product_id' => $product->id,
            ],
            subjectId: $rating->id,
            subjectType: ProductRating::class
        );

        return response()->json(['message' => 'Product rating updated successfully.'], Response::HTTP_OK);
    }

    private function abortIfRatingDoesNotBelongToProduct(Product $product, ProductRating $rating): void
    {
        if ($rating->orderItem()->where('product_id', $product->id)->doesntExist()) {
            abort(Response::HTTP_NOT_FOUND);
        }
    }
}
