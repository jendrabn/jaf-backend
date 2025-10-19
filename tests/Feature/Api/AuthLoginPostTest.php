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
