<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateWishlistRequest;
use App\Http\Requests\Api\DeleteWishlistRequest;
use App\Http\Resources\WishlistResource;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class WishlistController extends Controller
{
    public function list(): JsonResponse
    {
        $wishlists = Wishlist::with(['product', 'product.media', 'product.category', 'product.brand'])
            ->where('user_id', auth()->id())
            ->latest('id')
            ->get();

        return WishlistResource::collection($wishlists)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function create(CreateWishlistRequest $request): JsonResponse
    {
        Wishlist::firstOrCreate(['user_id' => auth()->id()] + $request->validated());

        return response()->json(['data' => true], Response::HTTP_CREATED);
    }

    public function delete(DeleteWishlistRequest $request): JsonResponse
    {
        Wishlist::where('user_id', auth()->id())
            ->whereIn('id', $request->validated('wishlist_ids'))
            ->delete();

        return response()->json(['data' => true], Response::HTTP_OK);
    }
}
