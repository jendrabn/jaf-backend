<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\AuthController;
use App\Http\Requests\Api\ResendLoginOtpRequest;
use App\Models\LoginOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class AuthResendLoginOtpPostTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function resend_login_otp_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(
            AuthController::class,
            'resendLoginOtp',
            ResendLoginOtpRequest::class
        );
    }

    #[Test]
    public function resend_login_otp_request_has_the_correct_validation_rules()
    {
        $this->assertValidationRules([
            'email' => [
                'required',
                'string',
                'email',
            ],
        ], (new ResendLoginOtpRequest)->rules());
    }

    #[Test]
    public function can_resend_login_otp()
    {
        Mail::fake();
        config([
            'auth.otp_expiry' => 10,
            'auth.otp_resend_throttle' => 60,
        ]);

        $user = $this->createUser();
        $oldOtp = LoginOtp::query()->create([
            'user_id' => $user->id,
            'code' => '111111',
            'sent_to' => $user->email,
            'expires_at' => now()->addMinutes(5),
        ]);

        RateLimiter::clear('resend-login-otp:'.$user->id);

        $response = $this->postJson('/api/auth/resend_login_otp', [
            'email' => $user->email,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.otp_required', true);

        $this->assertDatabaseMissing('login_otps', ['id' => $oldOtp->id]);
        $this->assertDatabaseCount('login_otps', 1);
    }

    #[Test]
    public function cannot_resend_login_otp_while_throttled()
    {
        Mail::fake();
        config([
            'auth.otp_expiry' => 10,
            'auth.otp_resend_throttle' => 60,
        ]);

        $user = $this->createUser();
        RateLimiter::clear('resend-login-otp:'.$user->id);

        $firstResponse = $this->postJson('/api/auth/resend_login_otp', [
            'email' => $user->email,
        ]);
        $secondResponse = $this->postJson('/api/auth/resend_login_otp', [
            'email' => $user->email,
        ]);

        $firstResponse->assertOk();
        $secondResponse->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
