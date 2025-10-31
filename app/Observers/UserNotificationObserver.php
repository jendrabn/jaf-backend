<?php

namespace App\Observers;

use App\Jobs\SendPushNotificationJob;
use App\Models\UserNotification;

class UserNotificationObserver
{
    /**
     * Handle the UserNotification "created" event.
     */
    public function created(UserNotification $userNotification): void
    {
        // Dispatch job to send push notification
        SendPushNotificationJob::dispatch($userNotification);
    }

    /**
     * Handle the UserNotification "updated" event.
     */
    public function updated(UserNotification $userNotification): void
    {
        //
    }

    /**
     * Handle the UserNotification "deleted" event.
     */
    public function deleted(UserNotification $userNotification): void
    {
        //
    }

    /**
     * Handle the UserNotification "restored" event.
     */
    public function restored(UserNotification $userNotification): void
    {
        //
    }

    /**
     * Handle the UserNotification "force deleted" event.
     */
    public function forceDeleted(UserNotification $userNotification): void
    {
        //
    }
}
