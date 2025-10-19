<?php

namespace App\Services;

use App\Http\Requests\Api\GoogleLoginRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\ResendLoginOtpRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Http\Requests\Api\VerifyLoginOtpRequest;
use App\Mail\LoginOtpMail;
use App\Models\LoginOtp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthService
{
    public function login(LoginRequest $request): User
    {
        $validatedData = $request->validated();

        if (! auth()->attempt($validatedData)) {
            Log::warning('Login failed: invalid credentials.', ['email' => $validatedData['email']]);
            throw new AuthenticationException('Kredensial yang diberikan salah.');
        }

        /** @var User $user */
        $user = User::where('email', $validatedData['email'])->firstOrFail();

        // Generate 6-digit numeric OTP and expiry
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(10);

        // Remove any previous unconsumed OTPs for safety
        LoginOtp::query()
            ->where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->delete();

        // Create OTP record
        LoginOtp::create([
            'user_id' => $user->id,
            'code' => $code,
            'sent_to' => $user->email,
            'expires_at' => $expiresAt,
        ]);

        // Send OTP email (queued)
        Mail::to($user->email)->queue(new LoginOtpMail($code, Carbon::parse($expiresAt)));

        // Attach context properties for API response
        $user->setAttribute('otp_required', true);
        $user->setAttribute('otp_expires_at', $expiresAt);
        $user->setAttribute('otp_sent_to', $this->maskEmail($user->email));

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
            Log::error('Google login failed.', ['error' => $exception->getMessage()]);

            throw new AuthenticationException('Tidak dapat melakukan autentikasi dengan Google.');
        }

        $email = $googleUser->getEmail();

        if (empty($email)) {
            Log::warning('Google login failed: email not available.');
            throw ValidationException::withMessages([
                'token' => 'Akun Google tidak menyediakan alamat email.',
            ]);
        }

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        $name = $googleUser->getName() ?: $googleUser->getNickname() ?: $email;

        if (! $user) {
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

            if (! empty($updates)) {
                $user->forceFill($updates)->save();
            }
        }

        $user->auth_token = $user->createToken('auth_token')->plainTextToken;

        return $user;
    }

    public function verifyLoginOtp(VerifyLoginOtpRequest $request): User
    {
        $validated = $request->validated();

        /** @var User $user */
        $user = User::where('email', $validated['email'])->firstOrFail();

        $otp = LoginOtp::query()
            ->where('user_id', $user->id)
            ->where('code', $validated['code'])
            ->latest()
            ->first();

        if (! $otp) {
            Log::warning('OTP verification failed: invalid code.', ['user_id' => $user->id, 'email' => $validated['email']]);
            throw ValidationException::withMessages(['code' => 'Kode OTP tidak valid.']);
        }

        if (now()->isAfter($otp->expires_at)) {
            Log::warning('OTP verification failed: code expired.', ['user_id' => $user->id, 'email' => $validated['email']]);
            throw ValidationException::withMessages(['code' => 'Kode OTP telah kedaluwarsa.']);
        }

        if (! is_null($otp->consumed_at)) {
            Log::warning('OTP verification failed: code already used.', ['user_id' => $user->id, 'email' => $validated['email']]);
            throw ValidationException::withMessages(['code' => 'Kode OTP sudah digunakan.']);
        }

        $otp->forceFill(['consumed_at' => now()])->save();

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

        if ($status !== Password::PASSWORD_RESET) {
            Log::error('Password reset failed.', ['status' => $status, 'email' => $request->validated()['email'] ?? null]);
            throw ValidationException::withMessages(['email' => 'Gagal mereset kata sandi. Silakan coba lagi.']);
        }

        return $status;
    }

    public function resendLoginOtp(ResendLoginOtpRequest $request): User
    {
        $email = $request->validated()['email'];

        /** @var User $user */
        $user = User::where('email', $email)->firstOrFail();

        // Rate limit: satu permintaan setiap 30 detik per pengguna (berdasarkan waktu request)
        $key = 'resend-login-otp:'.$user->id;

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $wait = (int) RateLimiter::availableIn($key);
            $wait = max(1, min(30, $wait)); // batasi 1..30 detik
            Log::warning('Resend OTP rate limit reached.', ['user_id' => $user->id, 'email' => $user->email, 'wait' => $wait]);

            throw ValidationException::withMessages([
                'email' => "Harap tunggu {$wait} detik sebelum meminta OTP baru.",
            ]);
        }

        // Tandai percobaan dengan masa berlaku 30 detik
        RateLimiter::hit($key, 30);

        // Clear previous unconsumed OTPs
        LoginOtp::query()
            ->where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->delete();

        // Generate and send new OTP
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(10);

        LoginOtp::create([
            'user_id' => $user->id,
            'code' => $code,
            'sent_to' => $user->email,
            'expires_at' => $expiresAt,
        ]);

        Mail::to($user->email)->queue(new LoginOtpMail($code, Carbon::parse($expiresAt)));
        Log::info('Resent OTP successfully.', ['user_id' => $user->id, 'email' => $user->email]);

        // Return same shape as initial login OTP requirement
        $user->setAttribute('otp_required', true);
        $user->setAttribute('otp_expires_at', $expiresAt);
        $user->setAttribute('otp_sent_to', $this->maskEmail($user->email));

        return $user;
    }

    private function maskEmail(string $email): string
    {
        if (strpos($email, '@') === false) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $visible = max(1, min(3, strlen($local)));
        $maskedLocal = substr($local, 0, $visible).str_repeat('*', max(0, strlen($local) - $visible));

        return $maskedLocal.'@'.$domain;
    }
}
