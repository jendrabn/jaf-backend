<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Initialize Midtrans configuration from config/services.php.
     */
    public function initialize(): void
    {
        $serverKey = (string) Config::get('services.midtrans.server_key', '');
        $isProduction = (bool) Config::get('services.midtrans.is_production', false);

        MidtransConfig::$serverKey = $serverKey;
        MidtransConfig::$isProduction = $isProduction;

        // Recommended defaults
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;
    }

    /**
     * Create a Snap transaction for the given order and invoice.
     *
     * @return array{token:string,redirect_url:string}
     */
    public function createTransaction(Order $order, Invoice $invoice, User $user): array
    {
        $items = $order->items()->get();

        $itemDetails = [];
        foreach ($items as $item) {
            $price = (int) ($item->price_after_discount ?? $item->price);
            $itemDetails[] = [
                'id' => (string) $item->product_id,
                'price' => $price,
                'quantity' => (int) $item->quantity,
                'name' => (string) $item->name,
            ];
        }

        // Append shipping and tax so Midtrans displays and totals match invoice amount
        if ((int) $order->shipping_cost > 0) {
            $itemDetails[] = [
                'id' => 'shipping',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Shipping',
            ];
        }

        if ((int) $order->tax_amount > 0) {
            $itemDetails[] = [
                'id' => 'tax',
                'price' => (int) $order->tax_amount,
                'quantity' => 1,
                'name' => 'Tax',
            ];
        }

        if ((int) $order->discount > 0) {
            // Represent coupon discount (if any) as a negative line item
            $itemDetails[] = [
                'id' => 'discount',
                'price' => 0 - (int) $order->discount,
                'quantity' => 1,
                'name' => 'Discount',
            ];
        }

        // Ensure item_details sum equals invoice amount by adding final adjustment if needed
        $sum = 0;
        foreach ($itemDetails as $it) {
            $sum += ((int) $it['price']) * ((int) $it['quantity']);
        }
        $gross = (int) $invoice->amount;
        if ($sum !== $gross) {
            $diff = $gross - $sum;
            $itemDetails[] = [
                'id' => 'adjustment',
                'price' => $diff,
                'quantity' => 1,
                'name' => 'Adjustment',
            ];
        }

        $address = $user->address; // may be null
        $shippingAddress = $address ? Arr::get($address->toArray(), 'address') : null;

        $customerDetails = [
            'first_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'billing_address' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'address' => $shippingAddress ?? '',
            ],
            'shipping_address' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'address' => $shippingAddress ?? '',
            ],
        ];

        $params = [
            'transaction_details' => [
                // Use Order ID as order_id to align with business requirement
                'order_id' => (string) $order->id,
                'gross_amount' => (int) $invoice->amount,
            ],
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
        ];

        try {
            $snapTransaction = Snap::createTransaction($params);

            // Midtrans response can be object (stdClass) or array depending on SDK/version
            if (is_object($snapTransaction)) {
                $token = (string) ($snapTransaction->token ?? '');
                $redirectUrl = (string) ($snapTransaction->redirect_url ?? '');
            } else {
                $token = (string) ($snapTransaction['token'] ?? '');
                $redirectUrl = (string) ($snapTransaction['redirect_url'] ?? '');
            }

            return [
                'token' => $token,
                'redirect_url' => $redirectUrl,
            ];
        } catch (\Throwable $e) {
            Log::error('Midtrans createTransaction failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'invoice_id' => $invoice->id,
            ]);

            // Re-throw to let caller handle gracefully
            throw $e;
        }
    }
}
