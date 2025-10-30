<?php

namespace Tests\Feature;

use App\Mail\LoginOtpMail;
use Carbon\Carbon;
use Tests\TestCase;

class LoginOtpMailTest extends TestCase
{
    public function test_mailable_contains_code_and_expiration_notice(): void
    {
        $expiresAt = Carbon::create(2025, 10, 30, 12, 0, 0, 'Asia/Jakarta');
        $mailable = new LoginOtpMail('123456', $expiresAt);

        $html = $mailable->render();

        $this->assertStringContainsString('Verifikasi Login', $html);
        $this->assertStringContainsString('123456', $html);
        $this->assertStringContainsString('Kode berlaku sampai', $html);
        $this->assertStringContainsString($expiresAt->format('d-m-Y H:i'), $html);
        $this->assertStringContainsString('abaikan email ini', $html);
        $this->assertStringContainsString(e(config('app.name')), $html);
    }
}
