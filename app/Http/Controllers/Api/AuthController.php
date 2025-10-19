<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\GoogleLoginRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\ResendLoginOtpRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Http\Requests\Api\VerifyLoginOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated())->assignRole(User::ROLE_USER);

        return UserResource::make($user)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->login($request);

        return UserResource::make($user)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function loginWithGoogle(GoogleLoginRequest $request): JsonResponse
    {
        $user = $this->authService->loginWithGoogle($request);

        return UserResource::make($user)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function logout(): JsonResponse
    {
        $user = auth()->user();

        $user->currentAccessToken()->delete();

        return response()->json(['data' => true], Response::HTTP_OK);
    }

    public function sendPasswordResetLink(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink($request->validated());

        throw_if(
            $status !== Password::RESET_LINK_SENT,
            ValidationException::withMessages(['email' => trans($status)])
        );

        return response()->json(['data' => true], Response::HTTP_OK);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword($request);

        return response()->json(['data' => true], Response::HTTP_OK);
    }

    public function verifyLoginOtp(VerifyLoginOtpRequest $request): JsonResponse
    {
        $user = $this->authService->verifyLoginOtp($request);

        return UserResource::make($user)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function resendLoginOtp(ResendLoginOtpRequest $request): JsonResponse
    {
        $user = $this->authService->resendLoginOtp($request);

        return UserResource::make($user)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
