<?php

namespace Tests\Feature\Api;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class PaymentMidtransNotificationPostTest extends ApiTestCase
{
    use RefreshDatabase;

    private function signatureFor(string $orderId, string $statusCode, string $grossAmount): string
    {
        return hash('sha512', $orderId.$statusCode.$grossAmount.config('services.midtrans.server_key'));
    }

    #[Test]
    public function returns_bad_request_for_invalid_payload()
    {
        config(['services.midtrans.server_key' => 'server-key']);

        $response = $this->postJson('/api/payments/midtrans/notification', []);

        $response->assertBadRequest()
            ->assertJsonPath('message', 'Invalid payload');
    }

    #[Test]
    public function returns_unauthorized_for_invalid_signature()
    {
        config(['services.midtrans.server_key' => 'server-key']);

        $response = $this->postJson('/api/payments/midtrans/notification', [
            'order_id' => '1',
            'status_code' => '200',
            'gross_amount' => '150000',
            'signature_key' => 'invalid',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid signature');
    }

    #[Test]
    public function returns_not_found_when_invoice_does_not_exist()
    {
        config(['services.midtrans.server_key' => 'server-key']);

        $payload = [
            'order_id' => '999',
            'status_code' => '200',
            'gross_amount' => '150000',
        ];
        $payload['signature_key'] = $this->signatureFor(
            $payload['order_id'],
            $payload['status_code'],
            $payload['gross_amount'],
        );

        $response = $this->postJson('/api/payments/midtrans/notification', $payload);

        $response->assertNotFound()
            ->assertJsonPath('message', 'Invoice not found');
    }

    #[Test]
    public function settlement_notification_marks_invoice_as_paid()
    {
        config(['services.midtrans.server_key' => 'server-key']);

        $order = Order::factory()->for($this->createUser())->create([
            'status' => Order::STATUS_PENDING_PAYMENT,
        ]);
        $invoice = Invoice::factory()->for($order)->create([
            'amount' => 150000,
            'status' => Invoice::STATUS_UNPAID,
        ]);
        $payment = Payment::query()->create([
            'invoice_id' => $invoice->id,
            'method' => Payment::METHOD_BANK,
            'amount' => 150000,
            'status' => Payment::STATUS_PENDING,
            'info' => ['provider' => 'midtrans'],
        ]);

        $payload = [
            'order_id' => (string) $order->id,
            'status_code' => '200',
            'gross_amount' => '150000',
            'transaction_status' => 'settlement',
        ];
        $payload['signature_key'] = $this->signatureFor(
            $payload['order_id'],
            $payload['status_code'],
            $payload['gross_amount'],
        );

        $response = $this->postJson('/api/payments/midtrans/notification', $payload);

        $response->assertOk()
            ->assertJson(['data' => true]);

        $this->assertSame(Invoice::STATUS_PAID, $invoice->fresh()->status);
        $this->assertSame(Payment::STATUS_RELEASED, $payment->fresh()->status);
        $this->assertSame(Order::STATUS_PROCESSING, $order->fresh()->status);
        $this->assertSame('settlement', $payment->fresh()->info['midtrans']['status']);
    }
}
