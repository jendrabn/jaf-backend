<?php

namespace Tests\Feature;

use App\Mail\SubscribeNotificationMail;
use App\Models\Subscriber;
use Tests\TestCase;

class NewsletterSubscribeMailTest extends TestCase
{
    public function test_subscribe_mail_greets_subscriber_with_branding(): void
    {
        $subscriber = new Subscriber([
            'name' => 'Lia',
            'email' => 'lia@example.com',
        ]);

        $html = (new SubscribeNotificationMail($subscriber))->render();

        $this->assertStringContainsString('Selamat datang di newsletter kami', $html);
        $this->assertStringContainsString('Lia', $html);
        $this->assertStringContainsString(e(config('app.name')), $html);
        $this->assertStringContainsString('berhenti berlangganan', $html);
    }
}
