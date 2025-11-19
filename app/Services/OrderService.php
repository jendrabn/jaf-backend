<?php

namespace App\Services;

use App\Events\OrderCancelled;
use App\Events\OrderCompleted;
use App\Events\OrderCreated;
use App\Events\PaymentConfirmed;
use App\Http\Requests\Api\ConfirmPaymentRequest;
use App\Http\Requests\Api\CreateOrderRequest;
use App\Mail\OrderCreatedMail;
use App\Models\Bank;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Ewallet;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\Tax;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(public MidtransService $midtrans, public RajaOngkirService $rajaOngkirService) {}

    public function getOrders(Request $request, int $size = 10): LengthAwarePaginator
    {
        $page = $request->get('page', 1);

        $orders = Order::with([
            'items',
            'invoice',
            'items.product',
            'items.product.media',
            'items.product.category',
            'items.product.brand',
            'items.product.flashSales' => fn($query) => $query->where('is_active', true),
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
                    'oldest' => ['id', 'asc'],
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
                'order_id' => 'Order status must be pending payment.',
            ])
        );

        if (now()->isAfter($order->invoice->due_date)) {
            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'cancel_reason' => 'Order cancelled by system.',
            ]);

            // Dispatch OrderCancelled event for notification
            OrderCancelled::dispatch($order);

            throw ValidationException::withMessages([
                'order_id' => 'Order canceled, payment time has expired.',
            ]);
        }

        try {
            DB::transaction(function () use ($order, $request) {
                $paymentMethod = $order->invoice->payment->method;

                if ($paymentMethod === Payment::METHOD_BANK) {
                    $order->invoice->payment->bank()->create($request->validated());
                } elseif ($paymentMethod === Payment::METHOD_EWALLET) {
                    $order->invoice->payment->ewallet()->create($request->validated());
                } else {
                    throw ValidationException::withMessages([
                        'order_id' => 'Payment method not found.',
                    ]);
                }

                $order->update(['status' => Order::STATUS_PENDING]);

                // Dispatch PaymentConfirmed event for notification
                PaymentConfirmed::dispatch($order);
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
                'order_id' => 'Order status must be on delivery.',
            ])
        );

        try {
            DB::transaction(function () use ($order) {
                $order->update(['status' => Order::STATUS_COMPLETED]);
                $order->shipping->update(['status' => Shipping::STATUS_SHIPPED]);

                // Dispatch OrderCompleted event for notification
                OrderCompleted::dispatch($order);
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

        $shippingCosts = $this->rajaOngkirService->calculateCost(
            $shippingAddress['district_id'],
            $initialTotalWeight,
            $validatedData['shipping_courier']
        );

        $shippingService = collect($shippingCosts)->firstWhere('service', '=', $validatedData['shipping_service']);

        throw_if(
            ! $shippingService,
            ValidationException::withMessages([
                'shipping_service' => 'Shipping service is not available.',
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

            if (! empty($productIds)) {
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
            $this->applyEffectivePricing($lockedCarts, $user);

            $lockedTotalWeight = $this->totalWeight($lockedCarts);

            throw_if(
                $lockedTotalWeight !== $initialTotalWeight,
                ValidationException::withMessages([
                    'cart_ids' => 'The cart has been updated. Please review your items and try again.',
                ])
            );

            $totalPrice = $this->totalPrice($lockedCarts);
            $coupon = null;
            $discount = 0;

            if (! empty($validatedData['coupon_code'])) {
                $coupon = Coupon::query()
                    ->where('code', $validatedData['coupon_code'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                throw_if(
                    ! $coupon,
                    ValidationException::withMessages([
                        'coupon_code' => 'The coupon code is invalid.',
                    ])
                );

                if ($coupon->promo_type === 'limit') {
                    if ($coupon->limit !== null) {
                        $totalUsage = $coupon->usages()->lockForUpdate()->count();

                        throw_if(
                            $totalUsage >= $coupon->limit,
                            ValidationException::withMessages([
                                'coupon_code' => 'The coupon code has reached its usage limit.',
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
                                'coupon_code' => 'You have reached the usage limit for this coupon code.',
                            ])
                        );
                    }
                } elseif ($coupon->promo_type === 'period') {
                    $today = now()->startOfDay();

                    throw_if(
                        ($coupon->start_date && $today->lt($coupon->start_date)) ||
                            ($coupon->end_date && $today->gt($coupon->end_date)),
                        ValidationException::withMessages([
                            'coupon_code' => 'The coupon code is not valid at this time.',
                        ])
                    );
                } else {
                    throw ValidationException::withMessages([
                        'coupon_code' => 'The coupon code has an invalid promo type.',
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

            // Hitung fee payment gateway (Midtrans) FLAT dari konfigurasi .env jika metode pembayaran gateway
            $gatewayFeeFlat = (int) config('services.midtrans.fee_flat', 0);
            $gatewayFee = 0;
            $gatewayFeeName = null;
            if ($validatedData['payment_method'] === Payment::METHOD_GATEWAY) {
                $gatewayFee = $gatewayFeeFlat;
                $gatewayFeeName = 'Payment Gateway Fee';
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'discount' => $discount,
                'discount_name' => $coupon ? $coupon->name : null,
                'tax_amount' => $totalTax,
                'tax_name' => Tax::pluck('name')->join(', '),
                'shipping_cost' => $shippingCost,
                'gateway_fee' => $gatewayFee,
                'gateway_fee_name' => $gatewayFeeName,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'note' => $validatedData['note'],
            ]);

            foreach ($lockedCarts as $item) {
                throw_if(
                    ! $item->product,
                    ValidationException::withMessages([
                        'cart_ids' => 'One or more products are no longer available.',
                    ])
                );

                $unitPrice = (int) ($item->getAttribute('effective_price') ?? $this->effectiveItemPrice($item));
                $flashSaleId = $item->getAttribute('applied_flash_sale_id');

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product->id,
                    'name' => $item->product->name,
                    'weight' => $item->product->weight,
                    'price' => $unitPrice,
                    'discount_in_percent' => $item->product->discount_in_percent,
                    'price_after_discount' => $unitPrice,
                    'quantity' => $item->quantity,
                    'flash_sale_id' => $flashSaleId,
                ]);

                $item->product->decrement('stock', $item->quantity);

                if ($flashSaleId) {
                    $this->incrementFlashSaleSold($flashSaleId, $item->product->id, $item->quantity);
                }

                $item->delete();
            }

            $invoice = Invoice::create([
                'order_id' => $order->id,
                'number' => 'INV' . $order->created_at->format('dmy') . $order->id,
                'amount' => $totalAmount + $gatewayFee,
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
                    'account_number' => $bank->account_number,
                ];
            } elseif ($validatedData['payment_method'] === Payment::METHOD_EWALLET) {
                $ewallet = Ewallet::findOrFail($validatedData['ewallet_id']);
                $paymentInfo = [
                    'name' => $ewallet->name,
                    'account_name' => $ewallet->account_name,
                    'account_username' => $ewallet->account_username,
                    'phone' => $ewallet->phone,
                ];
            } elseif ($validatedData['payment_method'] === Payment::METHOD_GATEWAY) {
                // Initialize gateway (Midtrans) basic info untuk frontend + fee
                $paymentInfo = [
                    'provider' => 'midtrans',
                    'client_key' => config('services.midtrans.client_key'),
                    'fee' => $gatewayFee,
                ];
            } else {
                throw new \Exception('Payment method not found!');
            }

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'method' => $validatedData['payment_method'],
                'info' => $paymentInfo,
                'amount' => $totalAmount + $gatewayFee,
                'status' => Payment::STATUS_PENDING,
            ]);

            // If using payment gateway, create Midtrans Snap transaction and store token + redirect URL
            if ($validatedData['payment_method'] === Payment::METHOD_GATEWAY) {
                $snap = $this->midtrans->createTransaction($order, $invoice, $user);
                $info = $payment->info ?? [];
                $info['snap_token'] = $snap['token'] ?? '';
                $info['redirect_url'] = $snap['redirect_url'] ?? '';
                $payment->update(['info' => $info]);
            }

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
                    'address' => $userAddress->address,
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
                    'order_id' => $order->id,
                ]);

                // Dispatch delayed job to deactivate coupon if usage limit is reached
                DB::afterCommit(function () use ($coupon) {
                    \App\Jobs\DeactivateCouponIfLimitReached::dispatch($coupon->id)
                        ->onQueue('coupons')
                        ->delay(now()->addSeconds(5));
                });
            }

            DB::afterCommit(function () use ($user, $order, $invoice, $payment) {
                Mail::to($user->email)->queue(new OrderCreatedMail($order, $invoice, $payment));

                // Dispatch OrderCreated event for notification
                OrderCreated::dispatch($order);
            });

            // Schedule automatic cancellation via delayed queue message (24h after creation)
            DB::afterCommit(function () use ($order) {
                \App\Jobs\CancelOrderIfUnpaid::dispatch($order->id)
                    ->onQueue('orders-cancel')
                    ->delay($order->created_at->copy()->addDay());
            });

            return $order;
        });
    }

    public function validateBeforeCreateOrder(Collection $carts): void
    {
        throw_if(
            $carts->isEmpty(),
            ValidationException::withMessages([
                'cart_ids' => 'The carts must not be empty.',
            ])
        );

        throw_if(
            $carts->filter(fn($item) => ! $item->product->is_publish)->isNotEmpty(),
            ValidationException::withMessages([
                'cart_ids' => 'The product must be published.',
            ])
        );

        throw_if(
            $carts->filter(fn($item) => $item->quantity > $item->product->stock)->isNotEmpty(),
            ValidationException::withMessages([
                'cart_ids' => 'The quantity must not be greater than stock.',
            ])
        );

        throw_if(
            $this->totalWeight($carts) > Shipping::MAX_WEIGHT,
            ValidationException::withMessages([
                'cart_ids' => 'The total weight must not be greater than 25kg.',
            ])
        );
    }

    public function totalWeight(Collection $items): int
    {
        return $items->reduce(fn($carry, $item) => $carry + ($item->quantity * $item->product->weight));
    }

    public function totalPrice(Collection $items): int
    {
        return $items->reduce(function ($carry, $item) {
            return $carry + ($item->quantity * $this->effectiveItemPrice($item));
        }, 0);
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
        $dpp = max(0, $subtotal - max(0, $discount)); // dasar pengenaan pajak

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
        $tax = $this->totalTax($items, $discount);

        return max(0, $subtotal - max(0, $discount) + max(0, $shipping) + $tax);
    }

    private function effectiveItemPrice($item): int
    {
        $explicit = $item->getAttribute('effective_price');
        if ($explicit !== null) {
            return (int) $explicit;
        }

        $product = $item->product;

        if (! $product) {
            return 0;
        }

        if ($product->is_in_flash_sale && $product->flash_sale_price !== null) {
            return (int) round($product->flash_sale_price);
        }

        return (int) ($product->price_after_discount ?? $product->price ?? 0);
    }

    private function applyEffectivePricing(Collection $carts, User $user): void
    {
        foreach ($carts as $cart) {
            $pricing = $this->resolveFlashSalePricing($cart, $user);
            $cart->setAttribute('effective_price', $pricing['unit_price']);
            $cart->setAttribute('applied_flash_sale_id', $pricing['flash_sale_id']);
            $cart->setAttribute('flash_sale_end_at', $pricing['flash_sale_end_at']);
        }
    }

    private function resolveFlashSalePricing(Cart $cart, User $user): array
    {
        $product = $cart->product;
        if (! $product) {
            throw ValidationException::withMessages([
                'cart_ids' => 'One or more products are no longer available.',
            ]);
        }

        $unitPrice = (int) ($product->price_after_discount ?? $product->price ?? 0);
        $now = Carbon::now();

        $flashSaleRow = DB::table('flash_sale_products as fsp')
            ->join('flash_sales as fs', 'fs.id', '=', 'fsp.flash_sale_id')
            ->where('fsp.product_id', $product->id)
            ->where('fs.is_active', true)
            ->where('fs.start_at', '<=', $now)
            ->where('fs.end_at', '>=', $now)
            ->select([
                'fsp.flash_sale_id',
                'fsp.flash_price',
                'fsp.stock_flash',
                'fsp.sold',
                'fsp.max_qty_per_user',
                'fs.end_at',
            ])
            ->orderBy('fs.end_at')
            ->lockForUpdate()
            ->first();

        if (! $flashSaleRow) {
            return [
                'unit_price' => $unitPrice,
                'flash_sale_id' => null,
                'flash_sale_end_at' => null,
            ];
        }

        $available = (int) $flashSaleRow->stock_flash - (int) $flashSaleRow->sold;

        if ($cart->quantity > $available) {
            throw ValidationException::withMessages([
                'cart_ids' => sprintf('Flash sale stock is not enough for %s.', $product->name),
            ]);
        }

        $maxPerUser = (int) $flashSaleRow->max_qty_per_user;
        if ($maxPerUser > 0) {
            $previousQty = $this->userFlashSaleQuantity((int) $flashSaleRow->flash_sale_id, $user->id);

            if ($previousQty + $cart->quantity > $maxPerUser) {
                throw ValidationException::withMessages([
                    'cart_ids' => sprintf('You have reached the purchase limit for %s.', $product->name),
                ]);
            }
        }

        return [
            'unit_price' => (int) round($flashSaleRow->flash_price),
            'flash_sale_id' => (int) $flashSaleRow->flash_sale_id,
            'flash_sale_end_at' => Carbon::parse($flashSaleRow->end_at),
        ];
    }

    private function incrementFlashSaleSold(int $flashSaleId, int $productId, int $quantity): void
    {
        DB::table('flash_sale_products')
            ->where('flash_sale_id', $flashSaleId)
            ->where('product_id', $productId)
            ->increment('sold', $quantity);
    }

    private function userFlashSaleQuantity(int $flashSaleId, int $userId): int
    {
        return OrderItem::query()
            ->where('flash_sale_id', $flashSaleId)
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('status', '!=', Order::STATUS_CANCELLED);
            })
            ->sum('quantity');
    }
}
