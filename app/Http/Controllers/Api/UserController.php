<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProfileRequest;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Http\Resources\UserNotificationCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
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
            $fileName = uniqid().'_'.pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).'.jpeg';

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

    public function notifications(Request $request): JsonResponse
    {
        $user = auth()->user();

        $notifications = $user->notifications()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return UserNotificationCollection::make($notifications)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function markNotificationAsRead(UserNotification $notification): JsonResponse
    {
        $user = auth()->user();

        if ($notification->user_id !== $user->id) {
            return response()->json([
                'message' => 'Notification not found',
            ], Response::HTTP_FORBIDDEN);
        }

        $notification->markAsRead();

        return response()->json([
            'data' => true,
            'message' => 'Notification marked as read',
        ], Response::HTTP_OK);
    }

    public function markAllNotificationsAsRead(Request $request): JsonResponse
    {
        $user = auth()->user();

        $updated = $user->notifications()
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'data' => true,
            'message' => 'All notifications marked as read',
            'count' => $updated,
        ], Response::HTTP_OK);
    }

    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = auth()->user();

        $count = $user->notifications()
            ->unread()
            ->count();

        return response()->json([
            'data' => $count,
        ], Response::HTTP_OK);
    }
}
