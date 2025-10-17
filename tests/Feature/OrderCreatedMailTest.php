<?php

namespace Tests\Feature;

use App\Mail\OrderCreatedMail;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Carbon\Carbon;
use Tests\TestCase;

class OrderCreatedMailTest extends TestCase
{
    public function test_mailable_renders_order_summary_with_payment_info_and_due_date(): void
    {
        $order = new Order([
            'total_price' => 150000,
            'discount' => 10000,
            'discount_name' => 'Promo Oktober',
            'tax_amount' => 11000,
            'shipping_cost' => 20000,
        ]);

        $item1 = new OrderItem([
            'name' => 'Produk A',
            'price_after_discount' => 50000,
            'quantity' => 2,
        ]);

        $item2 = new OrderItem([
            'name' => 'Produk B',
            'price_after_discount' => 50000,
            'quantity' => 1,
        ]);

        $order->setRelation('items', collect([$item1, $item2]));

        $dueDate = Carbon::now()->addDay();
        $invoice = new Invoice([
            'number' => 'INV17101701',
            'amount' => 171000,
            'status' => Invoice::STATUS_UNPAID,
            'due_date' => $dueDate,
        ]);

        $payment = new Payment([
            'method' => Payment::METHOD_BANK,
            'info' => [
                'name' => 'BCA',
                'code' => '014',
                'account_name' => 'PT Example',
                'account_number' => '1234567890',
            ],
            'amount' => $invoice->amount,
            'status' => Payment::STATUS_PENDING,
        ]);

        $mailable = new OrderCreatedMail($order, $invoice, $payment);
        $html = $mailable->render();

        $rupiah = fn (int $v) => 'Rp '.number_format($v, 0, ',', '.');

        // Header and invoice number
        $this->assertStringContainsString('Pesanan Berhasil Dibuat', $html);
        $this->assertStringContainsString('Nomor Invoice:', $html);
        $this->assertStringContainsString($invoice->number, $html);

        // Product rows
        $this->assertStringContainsString('Produk A', $html);
        $this->assertStringContainsString('Produk B', $html);

        // Totals
        $this->assertStringContainsString($rupiah((int) $order->total_price), $html);
        $this->assertStringContainsString($rupiah((int) $order->discount), $html);
        $this->assertStringContainsString($rupiah((int) $order->tax_amount), $html);
        $this->assertStringContainsString($rupiah((int) $order->shipping_cost), $html);
        $this->assertStringContainsString('Total Bayar', $html);
        $this->assertStringContainsString($rupiah((int) $invoice->amount), $html);

        // Payment info
        $this->assertStringContainsString('Metode: Bank', $html);
        $this->assertStringContainsString('Nama: BCA', $html);
        $this->assertStringContainsString('Kode Bank: 014', $html);
        $this->assertStringContainsString('Nama Akun: PT Example', $html);
        $this->assertStringContainsString('Nomor Rekening: 1234567890', $html);

        // Due date
        $this->assertStringContainsString('Batas Waktu Pembayaran', $html);
        $this->assertStringContainsString($dueDate->format('d-m-Y H:i'), $html);
    }
}
