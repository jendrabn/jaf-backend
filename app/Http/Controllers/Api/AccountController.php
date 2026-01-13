<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProfileRequest;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends Controller
{
    public function get(): JsonResponse
    {
        $user = auth()->user();

        return UserResource::make($user)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function update(ProfileRequest $request): JsonResponse
    {
        $user = auth()->user();

        $user->update($request->validated());

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                $user->avatar->delete();
            }

            $file = $request->file('avatar');
            $fileName = uniqid() . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.jpeg';

            $imageBase64 = (new ImageManager(new Driver))->read($file)->toJpeg(50)->toDataUri();

            $user->addMediaFromBase64($imageBase64)
                ->setFileName($fileName)
                ->toMediaCollection(User::MEDIA_COLLECTION_NAME);
        }

        $user->fresh();

        return UserResource::make($user)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = auth()->user();

        $user->update(['password' => $request->validated('password')]);

        return response()->json(['data' => true], Response::HTTP_OK);
    }
}
