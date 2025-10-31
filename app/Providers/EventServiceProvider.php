<?php

namespace App\Providers;

use App\Models\UserNotification;
use App\Observers\UserNotificationObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        \App\Events\OrderCreated::class => [
            \App\Listeners\SendOrderCreatedNotification::class,
        ],

        \App\Events\PaymentConfirmed::class => [
            \App\Listeners\SendPaymentConfirmedNotification::class,
        ],

        \App\Events\OrderProcessing::class => [
            \App\Listeners\SendOrderProcessingNotification::class,
        ],

        \App\Events\OrderShipped::class => [
            \App\Listeners\SendOrderShippedNotification::class,
        ],

        \App\Events\OrderCompleted::class => [
            \App\Listeners\SendOrderCompletedNotification::class,
        ],

        \App\Events\OrderCancelled::class => [
            \App\Listeners\SendOrderCancelledNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        UserNotification::observe(UserNotificationObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
