<?php

namespace App\Mail;

use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Campaign mailable that renders HTML content created via Quill.
 */
class CampaignMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The email subject.
     */
    public string $subjectLine;

    /**
     * The HTML content for the email body.
     */
    public string $htmlBody;

    /**
     * The recipient subscriber (used for unsubscribe and tracking links).
     */
    public Subscriber $subscriber;

    /**
     * Optional campaign receipt id for tracking routes.
     */
    public ?int $receiptId;

    /**
     * Create a new message instance.
     */
    public function __construct(string $subjectLine, string $html, Subscriber $subscriber, ?int $receiptId = null)
    {
        $this->subjectLine = $subjectLine;
        $this->htmlBody = $html;
        $this->subscriber = $subscriber;
        $this->receiptId = $receiptId;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.campaign',
            with: [
                'subject' => $this->subjectLine,
                'html' => $this->htmlBody,
                'subscriber' => $this->subscriber,
                'receiptId' => $this->receiptId,
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
