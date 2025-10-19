<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateCartRequest;
use App\Http\Requests\Api\DeleteCartRequest;
use App\Http\Requests\Api\UpdateCartRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function list(): JsonResponse
    {
        $carts = Cart::with(['product', 'product.media', 'product.category', 'product.brand'])
            ->where('user_id', auth()->id())
            ->latest('id')
            ->get();

        return CartResource::collection($carts)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function create(CreateCartRequest $request): JsonResponse
    {
        $this->cartService->create($request);

        return response()->json(['data' => true], Response::HTTP_CREATED);
    }

    public function update(UpdateCartRequest $request, Cart $cart): JsonResponse
    {
        $this->cartService->update($request, $cart);

        return response()->json(['data' => true], Response::HTTP_OK);
    }

    public function delete(DeleteCartRequest $request): JsonResponse
    {
        Cart::where('user_id', auth()->id())
            ->whereIn('id', $request->validated('cart_ids'))
            ->delete();

        return response()->json(['data' => true], Response::HTTP_OK);
    }
}
