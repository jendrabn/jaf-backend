<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\AuthController;
use App\Http\Requests\Api\VerifyLoginOtpRequest;
use App\Models\LoginOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class AuthVerifyLoginOtpPostTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function verify_login_otp_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(
            AuthController::class,
            'verifyLoginOtp',
            VerifyLoginOtpRequest::class
        );
    }

    #[Test]
    public function verify_login_otp_request_has_the_correct_validation_rules()
    {
        $this->assertValidationRules([
            'email' => [
                'required',
                'string',
                'email',
            ],
            'code' => [
                'required',
                'string',
                'digits:6',
            ],
        ], (new VerifyLoginOtpRequest)->rules());
    }

    #[Test]
    public function can_verify_login_otp()
    {
        $user = $this->createUser(['email_verified_at' => null]);
        $otp = LoginOtp::query()->create([
            'user_id' => $user->id,
            'code' => '123456',
            'sent_to' => $user->email,
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/auth/verify_login_otp', [
            'email' => $user->email,
            'code' => '123456',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.email', $user->email);

        $this->assertNotNull($response['data']['auth_token']);
        $this->assertNotNull($otp->fresh()->consumed_at);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    #[Test]
    public function returns_validation_error_if_otp_code_is_invalid()
    {
        $user = $this->createUser();

        LoginOtp::query()->create([
            'user_id' => $user->id,
            'code' => '123456',
            'sent_to' => $user->email,
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/auth/verify_login_otp', [
            'email' => $user->email,
            'code' => '654321',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    #[Test]
    public function returns_validation_error_if_otp_code_has_expired()
    {
        $user = $this->createUser();

        LoginOtp::query()->create([
            'user_id' => $user->id,
            'code' => '123456',
            'sent_to' => $user->email,
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->postJson('/api/auth/verify_login_otp', [
            'email' => $user->email,
            'code' => '123456',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }
}
