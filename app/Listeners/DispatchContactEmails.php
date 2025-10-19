<?php

namespace App\Listeners;

use App\Events\ContactMessageCreated;

class DispatchContactEmails
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ContactMessageCreated $event): void
    {
        //
    }
}
