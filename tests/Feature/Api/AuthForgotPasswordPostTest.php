<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\AuthController;
use App\Http\Requests\Api\ForgotPasswordRequest;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class AuthForgotPasswordPostTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function send_password_reset_link_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(AuthController::class, 'sendPasswordResetLink', ForgotPasswordRequest::class);
    }

    #[Test]
    public function forgot_password_request_has_the_correct_validation_rules()
    {
        $this->assertValidationRules([
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email'),
            ],
        ], (new ForgotPasswordRequest)->rules());
    }

    #[Test]
    public function can_send_password_reset_link()
    {
        Notification::fake();

        $user = $this->createUser();

        $response = $this->postJson('/api/auth/forgot_password', $user->only('email'));

        $response->assertOk()
            ->assertJson(['data' => true]);

        Notification::assertSentTo(
            $user,
            ResetPassword::class,
            function ($notification, $channels, $notifiable) use ($user) {
                $mail = $notification->toMail($user)->toArray();

                $this->assertEquals(
                    config('shop.front_url')."/auth/reset_password?email={$user->email}&token={$notification->token}",
                    $mail['actionUrl']
                );

                return true;
            }
        );
    }
}
