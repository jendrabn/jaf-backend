<?php

namespace App\Services;

use App\Http\Requests\Api\{ConfirmPaymentRequest, CreateOrderRequest};
use App\Models\{Bank, Cart, Coupon, Invoice, Order, OrderItem, Payment, Product, Shipping, Tax};
use App\Models\Ewallet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Str;

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
                $paymentMethod = $order->invoice->payment->method;

                if ($paymentMethod === Payment::METHOD_BANK) {
                    $order->invoice->payment->bank()->create($request->validated());
                } else if ($paymentMethod === Payment::METHOD_EWALLET) {
                    $order->invoice->payment->ewallet()->create($request->validated());
                } else {
                    throw ValidationException::withMessages([
                        'order_id' => 'Payment method not found.'
                    ]);
                }

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
        $initialCarts = Cart::query()
            ->where('user_id', $user->id)
            ->whereIn('id', $validatedData['cart_ids'])
            ->with('product')
            ->get();

        $this->validateBeforeCreateOrder($initialCarts);

        $shippingAddress = $validatedData['shipping_address'];
        $initialTotalWeight = $this->totalWeight($initialCarts);

        $shippingCosts = (new RajaOngkirService())->calculateCost(
            $shippingAddress['district_id'],
            $initialTotalWeight,
            $validatedData['shipping_courier']
        );

        $shippingService = collect($shippingCosts)->firstWhere('service', '=', $validatedData['shipping_service']);

        throw_if(
            !$shippingService,
            ValidationException::withMessages([
                'shipping_service' => 'Shipping service is not available.'
            ])
        );

        $shippingCost = $shippingService['cost'];

        return DB::transaction(function () use ($validatedData, $user, $shippingAddress, $shippingService, $shippingCost, $initialTotalWeight) {
            $lockedCarts = Cart::query()
                ->where('user_id', $user->id)
                ->whereIn('id', $validatedData['cart_ids'])
                ->lockForUpdate()
                ->with('product')
                ->get();

            $productIds = $lockedCarts->pluck('product_id')->filter()->unique()->all();

            if (!empty($productIds)) {
                $lockedProducts = Product::query()
                    ->whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $lockedCarts->each(function (Cart $cart) use ($lockedProducts) {
                    if ($lockedProducts->has($cart->product_id)) {
                        $cart->setRelation('product', $lockedProducts->get($cart->product_id));
                    }
                });
            }

            $this->validateBeforeCreateOrder($lockedCarts);

            $lockedTotalWeight = $this->totalWeight($lockedCarts);

            throw_if(
                $lockedTotalWeight !== $initialTotalWeight,
                ValidationException::withMessages([
                    'cart_ids' => 'The cart has been updated. Please review your items and try again.'
                ])
            );

            $totalPrice = $this->totalPrice($lockedCarts);
            $coupon = null;
            $discount = 0;

            if (!empty($validatedData['coupon_code'])) {
                $coupon = Coupon::query()
                    ->where('code', $validatedData['coupon_code'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                throw_if(
                    !$coupon,
                    ValidationException::withMessages([
                        'coupon_code' => 'The coupon code is invalid.'
                    ])
                );

                if ($coupon->promo_type === 'limit') {
                    if ($coupon->limit !== null) {
                        $totalUsage = $coupon->usages()->lockForUpdate()->count();

                        throw_if(
                            $totalUsage >= $coupon->limit,
                            ValidationException::withMessages([
                                'coupon_code' => 'The coupon code has reached its usage limit.'
                            ])
                        );
                    }

                    if ($coupon->limit_per_user !== null) {
                        $userUsage = $coupon->usages()
                            ->where('user_id', $user->id)
                            ->lockForUpdate()
                            ->count();

                        throw_if(
                            $userUsage >= $coupon->limit_per_user,
                            ValidationException::withMessages([
                                'coupon_code' => 'You have reached the usage limit for this coupon code.'
                            ])
                        );
                    }
                } elseif ($coupon->promo_type === 'period') {
                    $today = now()->startOfDay();

                    throw_if(
                        ($coupon->start_date && $today->lt($coupon->start_date)) ||
                            ($coupon->end_date && $today->gt($coupon->end_date)),
                        ValidationException::withMessages([
                            'coupon_code' => 'The coupon code is not valid at this time.'
                        ])
                    );
                } else {
                    throw ValidationException::withMessages([
                        'coupon_code' => 'The coupon code has an invalid promo type.'
                    ]);
                }
            }

            if ($coupon) {
                if ($coupon->discount_type === 'fixed') {
                    $discount = $coupon->discount_amount;
                } elseif ($coupon->discount_type === 'percentage') {
                    $discount = ($coupon->discount_amount / 100) * $totalPrice;
                }

                $discount = min($discount, $totalPrice);
            }

            $totalTax = $this->totalTax($lockedCarts, $discount);
            $totalAmount = $this->grandTotal($lockedCarts, $shippingCost, $discount);

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'discount' => $discount,
                'discount_name' => $coupon ? $coupon->name : null,
                'tax_amount' => $totalTax,
                'tax_name' => Tax::pluck('name')->join(', '),
                'shipping_cost' => $shippingCost,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'note' => $validatedData['note'],
            ]);

            foreach ($lockedCarts as $item) {
                throw_if(
                    !$item->product,
                    ValidationException::withMessages([
                        'cart_ids' => 'One or more products are no longer available.'
                    ])
                );

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product->id,
                    'name' => $item->product->name,
                    'weight' => $item->product->weight,
                    'price' => $item->product->price,
                    'discount_in_percent' => $item->product->discount_in_percent,
                    'price_after_discount' => $item->product->price_after_discount,
                    'quantity' => $item->quantity,
                ]);

                $item->product->decrement('stock', $item->quantity);
                $item->delete();
            }

            $invoice = Invoice::create([
                'order_id' => $order->id,
                'number' => 'INV' . $order->created_at->format('dmy') . $order->id,
                'amount' => $totalAmount,
                'status' => Invoice::STATUS_UNPAID,
                'due_date' => $order->created_at->addDays(1),
            ]);

            $paymentInfo = [];

            if ($validatedData['payment_method'] === Payment::METHOD_BANK) {
                $bank = Bank::findOrFail($validatedData['bank_id']);
                $paymentInfo = [
                    'name' => $bank->name,
                    'code' => $bank->code,
                    'account_name' => $bank->account_name,
                    'account_number' => $bank->account_number
                ];
            } elseif ($validatedData['payment_method'] === Payment::METHOD_EWALLET) {
                $ewallet = Ewallet::findOrFail($validatedData['ewallet_id']);
                $paymentInfo = [
                    'name' => $ewallet->name,
                    'account_name' => $ewallet->account_name,
                    'account_username' => $ewallet->account_username,
                    'phone' => $ewallet->phone,
                ];
            } else {
                throw new \Exception('Payment method not found!');
            }

            Payment::create([
                'invoice_id' => $invoice->id,
                'method' => $validatedData['payment_method'],
                'info' => $paymentInfo,
                'amount' => $totalAmount,
                'status' => Payment::STATUS_PENDING
            ]);

            $userAddress = $user->address()->updateOrCreate(['user_id' => $user->id], $shippingAddress);

            Shipping::create([
                'order_id' => $order->id,
                'address' => [
                    'name' => $userAddress->name,
                    'phone' => $userAddress->phone,
                    'province' => $userAddress->province['name'] ?? '',
                    'city' => $userAddress->city['name'] ?? '',
                    'district' => $userAddress->district['name'] ?? '',
                    'subdistrict' => $userAddress->subdistrict['name'] ?? '',
                    'zip_code' => $userAddress->zip_code,
                    'address' => $userAddress->address
                ],
                'courier' => $shippingService['courier'],
                'courier_name' => $shippingService['courier_name'],
                'service' => $shippingService['service'],
                'service_name' => $shippingService['service_name'],
                'etd' => $shippingService['etd'],
                'weight' => $lockedTotalWeight,
                'status' => Shipping::STATUS_PENDING,
            ]);

            if ($coupon) {
                $coupon->usages()->create([
                    'user_id' => $user->id,
                    'order_id' => $order->id
                ]);
            }

            return $order;
        });
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
        return $items->reduce(fn($carry, $item) => $carry + ($item->quantity * $item->product->price_after_discount), 0);
    }

    public function totalQuantity(Collection $items): int
    {
        return $items->reduce(fn($carry, $item) => $carry + $item->quantity);
    }

    /**
     * Hitung total pajak.
     * DPP = subtotal produk - discount (>= 0). Ongkir TIDAK dikenai pajak.
     * Kembalikan rupiah sebagai integer (hindari float untuk uang).
     */
    public function totalTax(Collection $items, int $discount = 0): int
    {
        $subtotal = (int) $this->totalPrice($items);       // harus integer (rupiah)
        $dpp      = max(0, $subtotal - max(0, $discount)); // dasar pengenaan pajak

        // Jumlahkan semua pajak dengan presisi desimal, lalu BULATKAN SEKALI di akhir
        $sum = Tax::query()->get()->reduce(function (float $carry, Tax $tax) use ($dpp) {
            $rate = (float) $tax->rate;                    // contoh: 11.00, 29.00
            return $carry + ($dpp * $rate / 100);
        }, 0.0);

        return (int) round($sum, 0, PHP_ROUND_HALF_UP);    // bulat ke rupiah
    }

    /**
     * Total bayar = (subtotal - discount) + ongkir + pajak
     * (pajak dihitung seperti di atas: tidak kena ongkir)
     */
    public function grandTotal(Collection $items, int $shipping = 0, int $discount = 0): int
    {
        $subtotal = (int) $this->totalPrice($items);
        $tax      = $this->totalTax($items, $discount);

        return max(0, $subtotal - max(0, $discount) + max(0, $shipping) + $tax);
    }
}
