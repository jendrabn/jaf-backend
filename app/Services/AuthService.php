<?php

namespace App\Services;

use App\Http\Requests\Api\{GoogleLoginRequest, LoginRequest, ResetPasswordRequest};
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthService
{
  public function login(LoginRequest $request): User
  {
    $validatedData = $request->validated();

    throw_if(
      !auth()->attempt($validatedData),
      new AuthenticationException('The provided credentials are incorrect.')
    );

    $user = User::where('email', $validatedData['email'])->firstOrFail();

    $user->auth_token = $user->createToken('auth_token')->plainTextToken;

    return $user;
  }

  public function loginWithGoogle(GoogleLoginRequest $request): User
  {
    try {
      $googleUser = Socialite::driver('google')
        ->stateless()
        ->userFromToken($request->validated('token'));
    } catch (\Throwable $exception) {
      report($exception);

      throw new AuthenticationException('Unable to authenticate with Google.');
    }

    $email = $googleUser->getEmail();

    throw_if(
      empty($email),
      ValidationException::withMessages([
        'token' => 'Google account does not provide an email address.'
      ])
    );

    $user = User::query()
      ->where('google_id', $googleUser->getId())
      ->orWhere('email', $email)
      ->first();

    $name = $googleUser->getName() ?: $googleUser->getNickname() ?: $email;

    if (!$user) {
      $user = User::create([
        'name' => $name,
        'email' => $email,
        'google_id' => $googleUser->getId(),
        'email_verified_at' => now(),
        'password' => Str::password(32),
      ])->assignRole(User::ROLE_USER);
    } else {
      $updates = [];

      if (empty($user->google_id)) {
        $updates['google_id'] = $googleUser->getId();
      }

      if (empty($user->name) && $name) {
        $updates['name'] = $name;
      }

      if (is_null($user->email_verified_at)) {
        $updates['email_verified_at'] = now();
      }

      if (!empty($updates)) {
        $user->forceFill($updates)->save();
      }
    }

    $user->auth_token = $user->createToken('auth_token')->plainTextToken;

    return $user;
  }

  public function resetPassword(ResetPasswordRequest $request): string
  {
    $status = Password::reset(
      $request->validated(),
      function (User $user, string $password) {
        $user->forceFill(['password' => $password])->setRememberToken(Str::random(60));
        $user->save();

        event(new PasswordReset($user));
      }
    );

    throw_if(
      $status !== Password::PASSWORD_RESET,
      ValidationException::withMessages(['email' => $status])
    );

    return $status;
  }
}
