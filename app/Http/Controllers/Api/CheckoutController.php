<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
use App\Http\Requests\Api\ShippingCostRequest;
use App\Http\Resources\BankResource;
use App\Http\Resources\CartResource;
use App\Http\Resources\EwalletResource;
use App\Http\Resources\TaxResource;
use App\Http\Resources\UserAddressResource;
use App\Models\Bank;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Ewallet;
use App\Models\Tax;
use App\Services\OrderService;
use App\Services\RajaOngkirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends Controller
{
    public function __construct(private RajaOngkirService $rajaOngkirService, private OrderService $orderService) {}

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $carts = Cart::with([
            'product',
            'product.media',
            'product.category',
            'product.brand',
            'product.flashSales' => fn ($query) => $query->where('is_active', true),
        ])
            ->whereIn('id', $request->validated('cart_ids'))
            ->get();

        $this->orderService->validateBeforeCreateOrder($carts);

        $totalWeight = $this->orderService->totalWeight($carts);
        $totalQuantity = $this->orderService->totalQuantity($carts);
        $totalPrice = $this->orderService->totalPrice($carts);
        $totalTax = $this->orderService->totalTax($carts);

        $userAddress = auth()->user()->address;

        $shippingCosts = $userAddress
            ? $this->rajaOngkirService->calculateCost($userAddress->district_id, $totalWeight)
            : [];

        $banks = Bank::with(['media'])->get();
        $ewallets = Ewallet::with(['media'])->get();
        $taxes = Tax::all();

        return response()->json([
            'data' => [
                'shipping_address' => $userAddress ? UserAddressResource::make($userAddress) : null,
                'carts' => CartResource::collection($carts),
                'shipping_methods' => $shippingCosts,
                'payment_methods' => [
                    'bank' => BankResource::collection($banks),
                    'ewallet' => EwalletResource::collection($ewallets),
                    'gateway' => [
                        'provider' => 'midtrans',
                        'client_key' => config('services.midtrans.client_key'),
                        // Show configured gateway fee on checkout
                        'fee' => (int) config('services.midtrans.fee_flat', 0),
                    ],
                ],
                'taxes' => TaxResource::collection($taxes),
                'total_quantity' => $totalQuantity,
                'total_weight' => $totalWeight,
                'total_price' => $totalPrice,
                'total_tax' => $totalTax,
            ],
        ], Response::HTTP_OK);
    }

    public function shippingCosts(ShippingCostRequest $request): JsonResponse
    {
        $costs = $this->rajaOngkirService->calculateCost($request->get('destination'), $request->get('weight'));

        return response()->json(['data' => $costs], Response::HTTP_OK);
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'cart_ids' => 'required|array',
            'cart_ids.*' => 'integer|exists:carts,id',
        ]);

        $coupon = Coupon::query()->where('code', $request->get('code'))->first();

        if (! $coupon) {
            return response()->json([
                'message' => 'Invalid coupon code',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Validate if coupon is active
        if (! $coupon->is_active) {
            return response()->json([
                'message' => 'This coupon is not active',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check coupon validity period
        $now = now();
        if ($coupon->start_date && $now->lt($coupon->start_date)) {
            return response()->json([
                'message' => 'This coupon is not yet valid',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($coupon->end_date && $now->gt($coupon->end_date)) {
            return response()->json([
                'message' => 'This coupon has expired',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check usage limit
        if ($coupon->limit && $coupon->usages()->count() >= $coupon->limit) {
            return response()->json([
                'message' => 'This coupon has reached its usage limit',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Store coupon in session for later use during checkout
        session(['applied_coupon' => $coupon->id]);

        return response()->json([
            'message' => 'Coupon applied successfully',
            'data' => $coupon,
        ], Response::HTTP_OK);
    }

    public function removeCoupon(Request $request, Coupon $coupon): JsonResponse
    {
        // Remove coupon from session
        session()->forget('applied_coupon');

        return response()->json([
            'message' => 'Coupon removed successfully',
        ], Response::HTTP_OK);
    }
}
