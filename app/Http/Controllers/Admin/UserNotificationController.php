<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\UserNotificationsDataTable;
use App\Enums\NotificationCategory;
use App\Enums\NotificationLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserNotificationRequest;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(UserNotificationsDataTable $dataTable)
    {
        return $dataTable->render('admin.user-notifications.index');
    }

    /**
     * Create a new user notification.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $users = User::pluck('name', 'id');
        $categories = NotificationCategory::options();
        $levels = NotificationLevel::options();

        return view('admin.user-notifications.create', compact('users', 'categories', 'levels'));
    }

    /**
     * Store a newly created user notification in the database.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(UserNotificationRequest $request)
    {
        $data = $request->validated();

        // Handle send to all users
        if ($data['send_to_all'] ?? false) {
            $users = User::all();

            foreach ($users as $user) {
                UserNotification::create([
                    'user_id' => $user->id,
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'category' => $data['category'],
                    'level' => $data['level'],
                    'url' => $data['url'] ?? null,
                    'icon' => $data['icon'] ?? null,
                    'meta' => $data['meta'] ?? null,
                ]);
            }

            $message = 'Notification sent to all users successfully.';
        } else {
            UserNotification::create($data);
            $message = 'Notification created successfully.';
        }

        toastr($message, 'success');

        return redirect()->route('admin.user-notifications.index');
    }

    /**
     * Display the specified user notification.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function show(UserNotification $userNotification)
    {
        $userNotification->load('user');

        return view('admin.user-notifications.show', compact('userNotification'));
    }

    /**
     * Edit a user notification.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(UserNotification $userNotification)
    {
        $users = User::pluck('name', 'id');
        $categories = NotificationCategory::options();
        $levels = NotificationLevel::options();

        return view('admin.user-notifications.edit', compact('users', 'categories', 'levels', 'userNotification'));
    }

    /**
     * Updates a user notification with the provided data.
     */
    public function update(UserNotificationRequest $request, UserNotification $userNotification): RedirectResponse
    {
        $userNotification->update($request->validated());

        toastr('User notification updated successfully.', 'success');

        return back();
    }

    /**
     * Deletes a user notification by ID.
     */
    public function destroy(UserNotification $userNotification): JsonResponse
    {
        $userNotification->delete();

        return response()->json(['message' => 'User notification deleted successfully.']);
    }

    /**
     * Deletes multiple user notifications by IDs.
     */
    public function massDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:user_notifications,id',
        ]);

        $ids = $request->ids;
        $count = count($ids);

        UserNotification::whereIn('id', $ids)->delete();

        audit_log(
            event: 'bulk_deleted',
            description: 'admin:bulk_delete_user_notifications',
            before: null,
            after: null,
            extra: [
                'changed' => ['ids' => $ids, 'count' => $count],
                'properties' => ['count' => $count],
                'meta' => ['note' => 'Bulk deleted '.$count.' user notifications'],
            ],
            subjectId: null,
            subjectType: UserNotification::class
        );

        return response()->json(['message' => 'User notifications deleted successfully.']);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(UserNotification $userNotification): JsonResponse
    {
        $userNotification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread(UserNotification $userNotification): JsonResponse
    {
        $userNotification->markAsUnread();

        return response()->json(['message' => 'Notification marked as unread.']);
    }
}
