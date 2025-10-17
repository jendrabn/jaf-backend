<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance with promoted, typed properties.
     */
    public function __construct(
        public Order $order,
        public Invoice $invoice,
        public Payment $payment
    ) {
        // Ensure items relation is available for the template
        $this->order->loadMissing('items');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pesanan Berhasil Dibuat',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.created',
            with: [
                'order' => $this->order,
                'invoice' => $this->invoice,
                'payment' => $this->payment,
                'paymentInfo' => $this->payment->info,
                'dueDate' => $this->invoice->due_date,
                'totalAmount' => $this->invoice->amount,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
