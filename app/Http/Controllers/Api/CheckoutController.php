<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\{CheckoutRequest, ShippingCostRequest};
use App\Http\Resources\{
    BankResource,
    CartResource,
    UserAddressResource
};
use App\Http\Resources\EwalletResource;
use App\Models\Ewallet;
use App\Services\{OrderService, RajaOngkirService};
use App\Models\{Bank, Cart};
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends Controller
{
    public function __construct(private RajaOngkirService $rajaOngkirService, private OrderService $orderService)
    {
    }

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $carts = Cart::with(['product', 'product.media', 'product.category', 'product.brand'])
            ->whereIn('id', $request->validated('cart_ids'))
            ->get();

        $this->orderService->validateBeforeCreateOrder($carts);

        $totalWeight = $this->orderService->totalWeight($carts);
        $totalQuantity = $this->orderService->totalQuantity($carts);
        $totalPrice = $this->orderService->totalPrice($carts);

        $userAddress = auth()->user()->address?->load(['city', 'city.province']);

        $shippingCosts = $userAddress
            ? $this->rajaOngkirService->getCosts($userAddress->city_id, $totalWeight)
            : [];

        $banks = Bank::with(['media'])->get();
        $ewallets = Ewallet::with(['media'])->get();

        return response()->json([
            'data' => [
                'shipping_address' => $userAddress ? UserAddressResource::make($userAddress) : null,
                'carts' => CartResource::collection($carts),
                'shipping_methods' => $shippingCosts,
                'payment_methods' => [
                    'bank' => BankResource::collection($banks),
                    'ewallet' => EwalletResource::collection($ewallets)
                ],
                'total_quantity' => $totalQuantity,
                'total_weight' => $totalWeight,
                'total_price' => $totalPrice,
            ]
        ], Response::HTTP_OK);
    }

    public function shippingCosts(ShippingCostRequest $request): JsonResponse
    {
        $costs = $this->rajaOngkirService->getCosts(...$request->validated());

        return response()->json(['data' => $costs], Response::HTTP_OK);
    }
}
