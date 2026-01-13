<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserNotificationCollection;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
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

    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = auth()->user();
        $user->update(['fcm_token' => $request->fcm_token]);

        return response()->json([
            'data' => true,
            'message' => 'FCM token updated successfully',
        ], Response::HTTP_OK);
    }
}
