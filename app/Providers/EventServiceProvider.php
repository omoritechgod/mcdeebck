<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\UserSubscribed;
use App\Listeners\SendSubscriptionConfirmation;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserSubscribed::class => [
            SendSubscriptionConfirmation::class,
        ],
    ];
    
    public function boot()
    {
        parent::boot();
    }
}
