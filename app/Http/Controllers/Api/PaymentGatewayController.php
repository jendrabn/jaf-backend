<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    public function midtransNotification(Request $request): JsonResponse
    {
        $payload = $request->all();

        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');
        $serverKey = (string) config('services.midtrans.server_key', '');

        if ($orderId === '' || $signatureKey === '' || $serverKey === '') {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $computedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if (! hash_equals($computedSignature, $signatureKey)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $invoice = Invoice::with(['payment', 'order'])
            ->where('order_id', (int) $orderId)
            ->first();

        if (! $invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');

        switch ($transactionStatus) {
            case 'capture':
                // credit card only
                if ($fraudStatus === 'accept') {
                    $this->markPaid($invoice);
                } else {
                    $this->markPending($invoice);
                }
                break;

            case 'settlement':
                $this->markPaid($invoice);
                break;

            case 'pending':
                $this->markPending($invoice);
                break;

            case 'expire':
            case 'cancel':
            case 'deny':
                $this->markCancelled($invoice);
                break;

            default:
                // Unknown status: do nothing
                break;
        }

        // Persist last midtrans status into payment info for auditing
        if ($invoice->payment) {
            $info = $invoice->payment->info ?? [];
            $info['midtrans'] = [
                'status' => $transactionStatus,
            ];
            $invoice->payment->update(['info' => $info]);
        }

        return response()->json(['data' => true]);
    }

    protected function markPaid(Invoice $invoice): void
    {
        $invoice->update(['status' => Invoice::STATUS_PAID]);

        if ($invoice->payment) {
            // Keep existing info payload shape
            $invoice->payment->update(['status' => Payment::STATUS_RELEASED]);
        }

        if ($invoice->order) {
            // Move order to processing after successful payment
            $invoice->order->update(['status' => Order::STATUS_PROCESSING]);
        }
    }

    protected function markPending(Invoice $invoice): void
    {
        if ($invoice->payment) {
            $invoice->payment->update(['status' => Payment::STATUS_PENDING]);
        }
    }

    protected function markCancelled(Invoice $invoice): void
    {
        if ($invoice->payment) {
            $invoice->payment->update(['status' => Payment::STATUS_CANCELLED]);
        }

        if ($invoice->order && $invoice->order->status !== Order::STATUS_COMPLETED) {
            $invoice->order->update([
                'status' => Order::STATUS_CANCELLED,
                'cancel_reason' => 'Cancelled by payment gateway',
            ]);
        }
    }
}
