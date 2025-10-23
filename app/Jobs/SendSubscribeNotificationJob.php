<?php

namespace App\Jobs;

use App\Mail\SubscribeNotificationMail;
use App\Models\Subscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSubscribeNotificationJob implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Subscriber $subscriber
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->subscriber->email)->send(new SubscribeNotificationMail($this->subscriber));
    }
}
