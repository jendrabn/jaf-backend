<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewContactMessageMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $message) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Contact Message #'.$this->message->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact.new_message',
            with: [
                'message' => $this->message,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
