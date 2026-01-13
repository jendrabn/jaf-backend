<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\AuthController;
use App\Http\Requests\Api\LoginRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class AuthLoginPostTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function login_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(
            AuthController::class,
            'login',
            LoginRequest::class
        );
    }

    #[Test]
    public function login_request_has_the_correct_validation_rules()
    {
        $this->assertValidationRules([
            'email' => [
                'required',
                'string',
                'email',
            ],
            'password' => [
                'required',
                'string',
            ],
        ], (new LoginRequest)->rules());
    }

    #[Test]
    public function can_login()
    {
        config(['auth.otp_enabled' => false]);
        $user = $this->createUser(['password' => $password = 'Secret123']);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'sex',
                    'birth_date',
                    'auth_token',
                ],
            ])
            ->assertJson([
                'data' => $this->formatUserData($user),
            ]);

        $this->assertCount(1, $user->fresh()->tokens);
    }

    #[Test]
    public function login_with_otp_enabled_accepts_string_config_values()
    {
        config([
            'auth.otp_enabled' => true,
            'auth.otp_expiry' => '10',
            'auth.otp_resend_throttle' => '60',
        ]);

        $user = $this->createUser(['password' => $password = 'Secret123']);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'sex',
                    'birth_date',
                    'otp_required',
                    'otp_expires_at',
                    'otp_sent_to',
                    'otp_resend_available_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'otp_required' => true,
                ],
            ]);

        $this->assertCount(0, $user->fresh()->tokens);
        $this->assertDatabaseCount('login_otps', 1);
    }

    #[Test]
    public function returns_unauthenticated_error_if_email_doenot_exist()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid@gmail.com',
            'password' => 'Secret123',
        ]);

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function returns_unauthenticated_error_if_password_is_incorrect()
    {
        $user = $this->createUser(['password' => 'Secret123']);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'Wrong-Password',
        ]);

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }
}
