<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\AuthController;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class AuthRegisterPostTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function register_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(
            AuthController::class,
            'register',
            RegisterRequest::class
        );
    }

    #[Test]
    public function register_request_has_the_correct_validation_rules()
    {
        $this->assertValidationRules([
            'name' => [
                'required',
                'string',
                'min:1',
                'max:30',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'min:1',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => [
                'required',
                'string',
                Password::min(8)->mixedCase()->numbers(),
                'max:30',
                'confirmed',
            ],
        ], (new RegisterRequest)->rules());
    }

    #[Test]
    public function can_register()
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $data = [
            'name' => 'Umar',
            'email' => 'umar@gmail.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => null,
                    'sex' => null,
                    'birth_date' => null,
                ],
            ]);

        $this->assertDatabaseCount('users', 1)
            ->assertDatabaseHas('users', Arr::only($data, ['name', 'email']));

        $user = User::whereEmail($data['email'])->first();

        $this->assertTrue(Hash::check($data['password'], $user->password));
        $this->assertTrue($user->hasRole(User::ROLE_USER));
    }
}
