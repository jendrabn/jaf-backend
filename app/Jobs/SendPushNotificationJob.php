<?php

namespace App\Jobs;

use App\Models\UserNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendPushNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = [5, 10, 30];

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public UserNotification $notification
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle($firebaseService = null): void
    {
        // Resolve FirebaseService if not provided
        if ($firebaseService === null) {
            $firebaseService = app(FirebaseService::class);
        }

        $user = $this->notification->user;

        if (!$user || !$user->fcm_token) {
            Log::info('Push notification FCM dilewati: User atau FCM token tidak ditemukan', [
                'notification_id' => $this->notification->id,
                'user_id' => $this->notification->user_id,
                'user_exists' => $user ? true : false,
                'has_fcm_token' => $user ? !empty($user->fcm_token) : false,
                'title' => $this->notification->title,
                'body' => $this->notification->body,
            ]);
            return;
        }

        try {
            $notificationData = [
                'title' => $this->notification->title,
                'body' => $this->notification->body,
                'click_action' => $this->notification->url ?? 'FLUTTER_NOTIFICATION_CLICK',
            ];

            $customData = [
                'notification_id' => (string) $this->notification->id,
                'category' => $this->notification->category,
                'level' => $this->notification->level,
                'url' => $this->notification->url ?? '',
                'icon' => $this->notification->icon ?? '',
            ];

            // Add meta data if exists
            if ($this->notification->meta) {
                foreach ($this->notification->meta as $key => $value) {
                    $customData['meta_' . $key] = is_array($value) ? json_encode($value) : (string) $value;
                }
            }

            $result = $firebaseService->sendNotification(
                $user->fcm_token,
                $notificationData,
                $customData
            );

            if ($result['success']) {
                Log::info('Push notification FCM berhasil dikirim', [
                    'notification_id' => $this->notification->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'fcm_token' => substr($user->fcm_token, 0, 20) . '...',
                    'message_id' => $result['data']['name'] ?? null,
                    'title' => $this->notification->title,
                    'body' => $this->notification->body,
                    'category' => $this->notification->category,
                ]);
            } else {
                Log::error('Push notification FCM gagal dikirim', [
                    'notification_id' => $this->notification->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'response' => $result['data'],
                    'status' => $result['status'],
                    'title' => $this->notification->title,
                    'body' => $this->notification->body,
                ]);

                // If token is invalid, clear it
                if (
                    isset($result['data']['error']) &&
                    is_string($result['data']['error']) &&
                    (str_contains($result['data']['error'], 'UNREGISTERED') ||
                        str_contains($result['data']['error'], 'INVALID_ARGUMENT'))
                ) {
                    $user->update(['fcm_token' => null]);
                    Log::warning('FCM token tidak valid dihapus dari user', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'error' => $result['data']['error'],
                        'notification_id' => $this->notification->id,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Exception terjadi saat mengirim push notification FCM', [
                'notification_id' => $this->notification->id,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $e->getMessage(),
                'title' => $this->notification->title,
                'body' => $this->notification->body,
            ]);

            throw $e;
        }
    }
}
