<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\UserController;
use App\Http\Requests\Api\UpdatePasswordRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class UserChangePasswordPutTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function update_password_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(
            UserController::class,
            'updatePassword',
            UpdatePasswordRequest::class
        );
    }

    #[Test]
    public function update_password_request_has_the_correct_validation_rules()
    {
        $this->assertValidationRules([
            'current_password' => [
                'required',
                'string',
                'current_password',
            ],
            'password' => [
                'required',
                'string',
                Password::min(8)->mixedCase()->numbers(),
                'max:30',
                'confirmed',
            ],
        ], (new UpdatePasswordRequest)->rules());
    }

    #[Test]
    public function unauthenticated_user_cannot_update_password()
    {
        $response = $this->putJson('/api/user/change_password');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function can_update_password()
    {
        $user = $this->createUser(['password' => $password = 'OldPassword123']);
        $newPassword = 'NewPassword123';

        $data = [
            'current_password' => $password,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ];

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/user/change_password', $data);

        $response->assertOk()
            ->assertJson(['data' => true]);

        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
    }
}
