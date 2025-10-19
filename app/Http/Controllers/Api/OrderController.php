<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\{ConfirmPaymentRequest, CreateOrderRequest, ProductRatingRequest};
use App\Http\Resources\{OrderCollection, OrderDetailResource};
use App\Models\Order;
use App\Models\ProductRating;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function list(Request $request): JsonResponse
    {
        $orders = $this->orderService->getOrders($request);

        return OrderCollection::make($orders)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function create(CreateOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder($request);

        return response()->json([
            'data' => [
                'id' => $order->id,
                'total_amount' =>  $order->invoice->amount,
                'payment_method' =>  $order->invoice->payment->method,
                'payment_info' =>  $order->invoice->payment->info,
                'gateway_fee' => $order->gateway_fee,
                'gateway_fee_name' => $order->gateway_fee_name,
                'payment_due_date' =>  $order->invoice->due_date,
                'created_at' => $order->created_at
            ]
        ], Response::HTTP_CREATED);
    }

    public function get(Order $order)
    {
        throw_if($order->user_id !== auth()->user()->id, ModelNotFoundException::class);

        $order->load([
            'items',
            'items.product',
            'items.product.category',
            'items.product.brand',
            'items.rating',
            'invoice',
            'invoice.payment',
            'shipping'
        ]);

        return OrderDetailResource::make($order)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function confirmPayment(ConfirmPaymentRequest $request, Order $order)
    {
        $this->orderService->confirmPayment($request, $order);

        return response()->json(['data' => true], Response::HTTP_CREATED);
    }

    public function confirmDelivered(Order $order): JsonResponse
    {
        $this->orderService->confirmDelivered($order);

        return response()->json(['data' => true], Response::HTTP_OK);
    }

    public function addRating(ProductRatingRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        DB::transaction(function () use ($validatedData) {
            foreach ($validatedData['ratings'] as $rating) {
                ProductRating::query()->updateOrCreate([
                    'order_item_id' => $rating['order_item_id'],
                ], [
                    'rating' => $rating['rating'],
                    'comment' => $rating['comment'],
                    'is_anonymous' => $rating['is_anonymous']
                ]);
            }
        });

        return response()->json(['data' => true], Response::HTTP_OK);
    }
}
