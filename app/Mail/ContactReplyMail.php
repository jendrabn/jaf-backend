<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Models\ContactReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactReplyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ContactReply $reply, public ContactMessage $message) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->reply->subject ?: ('Reply for Ticket #'.$this->message->id),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact.reply',
            with: [
                'reply' => $this->reply,
                'contactMessage' => $this->message,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
