<?php

namespace App\Services;

use App\Http\Requests\Api\{ConfirmPaymentRequest, CreateOrderRequest};
use App\Models\{Bank, Cart, Invoice, Order, OrderItem, Payment, Shipping};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function getOrders(Request $request, int $size = 10): LengthAwarePaginator
    {
        $page = $request->get('page', 1);

        $orders = Order::with([
            'items',
            'invoice',
            'items.product',
            'items.product.media',
            'items.product.category',
            'items.product.brand'
        ])->where('user_id', auth()->id());

        $orders->when(
            $request->has('status'),
            fn($q) => $q->where('status', $request->get('status'))
        );

        $orders->when(
            $request->has('sort_by'),
            function ($q) use ($request) {
                $sorts = [
                    'newest' => ['id', 'desc'],
                    'oldest' => ['id', 'asc']
                ];

                $q->orderBy(...$sorts[$request->get('sort_by')] ?? $sorts['newest']);
            },
            fn($q) => $q->orderBy('id', 'desc')
        );

        $orders = $orders->paginate(perPage: $size, page: $page);

        return $orders;
    }

    public function confirmPayment(ConfirmPaymentRequest $request, Order $order): Order
    {
        throw_if($order->user_id !== auth()->id(), ModelNotFoundException::class);

        throw_if(
            $order->status !== Order::STATUS_PENDING_PAYMENT,
            ValidationException::withMessages([
                'order_id' => 'Order status must be pending payment.'
            ])
        );

        if (now()->isAfter($order->invoice->due_date)) {
            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'cancel_reason' => 'Order cancelled by system.'
            ]);

            throw ValidationException::withMessages([
                'order_id' => 'Order canceled, payment time has expired.'
            ]);
        }

        try {
            DB::transaction(function () use ($order, $request) {
                $order->invoice->payment->bank()->create($request->validated());
                $order->update(['status' => Order::STATUS_PENDING]);
            });
        } catch (QueryException $e) {
            throw $e;
        }

        return $order;
    }

    public function confirmDelivered(Order $order): Order
    {
        throw_if($order->user_id !== auth()->id(), ModelNotFoundException::class);

        throw_if(
            $order->status !== Order::STATUS_ON_DELIVERY,
            ValidationException::withMessages([
                'order_id' => 'Order status must be on delivery.'
            ])
        );

        try {
            DB::transaction(function () use ($order) {
                $order->update(['status' => Order::STATUS_COMPLETED]);
                $order->shipping->update(['status' => Shipping::STATUS_SHIPPED]);
            });
        } catch (QueryException $e) {
            throw $e;
        }

        return $order;
    }

    public function createOrder(CreateOrderRequest $request): Order
    {
        $validatedData = $request->validated();

        $user = auth()->user();
        $carts = Cart::where('user_id', $user->id)
            ->whereIn('id', $validatedData['cart_ids'])
            ->get();
        $bank = Bank::findOrFail($validatedData['bank_id']);

        $this->validateBeforeCreateOrder($carts);

        $shippingAddress = $validatedData['shipping_address'];
        $totalWeight = $this->totalWeight($carts);
        $shippingService = (new RajaOngkirService)->getService(
            $validatedData['shipping_service'],
            $shippingAddress['city_id'],
            $totalWeight,
            $validatedData['shipping_courier']
        );

        throw_if(
            !$shippingService,
            ValidationException::withMessages([
                'shipping_service' => 'Shipping service is not available.'
            ])
        );

        $totalPrice = $this->totalPrice($carts);
        $shippingCost = $shippingService['cost'];
        $totalAmount = $totalPrice + $shippingCost;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'shipping_cost' => $shippingCost,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'notes' => $validatedData['notes'],
            ]);

            foreach ($carts as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product->id,
                    'name' => $item->product->name,
                    'weight' => $item->product->weight,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                ]);

                $item->product->decrement('stock', $item->quantity);
                $item->delete();
            }

            $invoice = Invoice::create([
                'order_id' => $order->id,
                'number' => implode('/', ['INV', $order->created_at->format('YYYYMMDD'), $order->id]),
                'amount' => $totalAmount,
                'status' => Invoice::STATUS_UNPAID,
                'due_date' => $order->created_at->addDays(1),
            ]);

            $b = Payment::create([
                'invoice_id' => $invoice->id,
                'method' => $validatedData['payment_method'],
                'info' => [
                    'name' => $bank->name,
                    'code' => $bank->code,
                    'account_name' => $bank->account_name,
                    'account_number' => $bank->account_number
                ],
                'amount' => $totalAmount,
                'status' => Payment::STATUS_PENDING
            ]);

            $userAddress = $user->address()->updateOrCreate(['user_id' => $user->id], $shippingAddress);

            Shipping::create([
                'order_id' => $order->id,
                'address' => [
                    'name' => $userAddress->name,
                    'phone' => $userAddress->phone,
                    'province' => $userAddress->city->province->name,
                    'city' => $userAddress->city->name,
                    'district' => $userAddress->district,
                    'postal_code' => $userAddress->postal_code,
                    'address' => $userAddress->address
                ],
                'courier' => $shippingService['courier'],
                'courier_name' => $shippingService['courier_name'],
                'service' => $shippingService['service'],
                'service_name' => $shippingService['service_name'],
                'etd' => $shippingService['etd'],
                'weight' => $totalWeight,
                'status' => Shipping::STATUS_PENDING,
            ]);

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        }

        return $order;
    }

    public function validateBeforeCreateOrder(Collection $carts): void
    {
        throw_if(
            $carts->isEmpty(),
            ValidationException::withMessages([
                'cart_ids' => 'The carts must not be empty.'
            ])
        );

        throw_if(
            $carts->filter(fn($item) => !$item->product->is_publish)->isNotEmpty(),
            ValidationException::withMessages([
                'cart_ids' => 'The product must be published.'
            ])
        );

        throw_if(
            $carts->filter(fn($item) => $item->quantity > $item->product->stock)->isNotEmpty(),
            ValidationException::withMessages([
                'cart_ids' => 'The quantity must not be greater than stock.'
            ])
        );

        throw_if(
            $this->totalWeight($carts) > Shipping::MAX_WEIGHT,
            ValidationException::withMessages([
                'cart_ids' => 'The total weight must not be greater than 25kg.'
            ])
        );
    }

    public function totalWeight(Collection $items): int
    {
        return $items->reduce(fn($carry, $item) => $carry + ($item->quantity * $item->product->weight));
    }

    public function totalPrice(Collection $items): int
    {
        return $items->reduce(fn($carry, $item) => $carry + ($item->quantity * $item->product->price));
    }

    public function totalQuantity(Collection $items): int
    {
        return $items->reduce(fn($carry, $item) => $carry + $item->quantity);
    }
}
