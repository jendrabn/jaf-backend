<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\AuthController;
use App\Http\Requests\Api\ResetPasswordRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class AuthResetPasswordPutTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function reset_password_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(
            AuthController::class,
            'resetPassword',
            ResetPasswordRequest::class
        );
    }

    #[Test]
    public function reset_password_request_has_the_correct_validation_rules()
    {
        $this->assertValidationRules([
            'email' => [
                'required',
                'string',
                'email',
                Rule::exists('users', 'email'),
            ],
            'token' => [
                'required',
                'string',
            ],
            'password' => [
                'required',
                'string',
                PasswordRule::min(8)->mixedCase()->numbers(),
                'max:30',
                'confirmed',
            ],
        ], (new ResetPasswordRequest)->rules());
    }

    #[Test]
    public function can_reset_password()
    {
        $user = $this->createUser();
        $token = Password::createToken($user);
        $newPassword = 'newPassword123';

        $data = [
            'email' => $user->email,
            'token' => $token,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ];

        $response = $this->putJson('/api/auth/reset_password', $data);

        $response->assertOk()
            ->assertJson(['data' => true]);

        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', Arr::only($data, ['email', 'token']));
    }
}
