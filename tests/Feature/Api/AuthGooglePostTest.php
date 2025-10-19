<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\AuthController;
use App\Http\Requests\Api\GoogleLoginRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class AuthGooglePostTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function google_login_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(
            AuthController::class,
            'loginWithGoogle',
            GoogleLoginRequest::class
        );
    }

    #[Test]
    public function google_login_request_has_the_correct_validation_rules()
    {
        $this->assertValidationRules([
            'token' => [
                'required',
                'string',
            ],
        ], (new GoogleLoginRequest)->rules());
    }

    #[Test]
    public function can_login_existing_user_with_google()
    {
        $user = $this->createUser();
        $token = 'google-token';

        $socialiteUser = (new SocialiteUser)
            ->setId('google-id')
            ->setEmail($user->email)
            ->setName($user->name);

        $this->mockGoogleProvider($socialiteUser, $token);

        $response = $this->postJson('/api/auth/google', [
            'token' => $token,
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
                    'avatar',
                ],
            ])
            ->assertJson([
                'data' => $this->formatUserData($user->fresh()),
            ]);

        $user = $user->fresh();

        $this->assertSame('google-id', $user->google_id);
        $this->assertCount(1, $user->tokens);
    }

    #[Test]
    public function can_register_a_new_user_with_google()
    {
        $token = 'register-token';
        $email = 'new.user@example.com';

        $socialiteUser = (new SocialiteUser)
            ->setId('new-google-id')
            ->setEmail($email)
            ->setName('New Google User');

        $this->mockGoogleProvider($socialiteUser, $token);

        $response = $this->postJson('/api/auth/google', [
            'token' => $token,
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
                    'avatar',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => 'New Google User',
                    'email' => $email,
                    'phone' => null,
                    'sex' => null,
                    'birth_date' => null,
                ],
            ]);

        $user = User::query()->where('email', $email)->first();

        $this->assertNotNull($user);
        $this->assertSame('new-google-id', $user->google_id);
        $this->assertTrue($user->hasRole(User::ROLE_USER));
        $this->assertNotNull($user->email_verified_at);
        $this->assertCount(1, $user->tokens);
    }

    #[Test]
    public function returns_unauthorized_when_google_token_is_invalid()
    {
        $token = 'invalid-token';

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('userFromToken')->once()->with($token)->andThrow(new \Exception('Invalid token'));

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $response = $this->postJson('/api/auth/google', [
            'token' => $token,
        ]);

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function returns_validation_error_when_google_user_has_no_email()
    {
        $token = 'no-email-token';

        $socialiteUser = (new SocialiteUser)
            ->setId('no-email-id');

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('userFromToken')->once()->with($token)->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $response = $this->postJson('/api/auth/google', [
            'token' => $token,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['token']);
    }

    private function mockGoogleProvider(SocialiteUser $socialiteUser, string $token): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('userFromToken')->once()->with($token)->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);
    }
}
