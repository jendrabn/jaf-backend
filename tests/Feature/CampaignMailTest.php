<?php

namespace Tests\Feature;

use App\Mail\CampaignMail;
use App\Models\Subscriber;
use Tests\TestCase;

class CampaignMailTest extends TestCase
{
    public function test_campaign_mail_contains_custom_html_and_links(): void
    {
        $subscriber = new Subscriber([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'token' => 'test-token',
        ]);

        $htmlBody = '<h2>Promo Khusus</h2><p>Dapatkan diskon menarik minggu ini.</p>';
        $mailable = new CampaignMail('Promo Akhir Tahun', $htmlBody, $subscriber, 77);

        $html = $mailable->render();

        $this->assertStringContainsString('Promo Akhir Tahun', $html);
        $this->assertStringContainsString('Promo Khusus', $html);
        $this->assertStringContainsString('berhenti berlangganan', $html);
        $this->assertStringContainsString(route('unsubscribe', $subscriber->token), $html);
        $this->assertStringContainsString(route('newsletter.webview', ['receipt' => 77, 'token' => $subscriber->token]), $html);
        $this->assertStringContainsString(route('newsletter.track.open', ['receipt' => 77, 'token' => $subscriber->token]), $html);
    }
}
