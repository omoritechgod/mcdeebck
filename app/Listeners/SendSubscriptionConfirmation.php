<?php

namespace App\Listeners;

use App\Events\UserSubscribed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSubscriptionConfirmation
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
    public function handle(UserSubscribed $event): void
    {
        //
    }
}
