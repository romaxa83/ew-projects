<?php

namespace App\Providers;

use App\Events\BS\Vehicles\DeleteVehicleEvent;
use App\Events\BS\Vehicles\SyncVehicleEvent;
use App\Events\BS\Vehicles\ToggleUseBSEvent;
use App\Events\OrderModifyEvent;
use App\Events\Orders\OrderUpdateEvent;
use App\Events\OrderStatusChanged;
use App\Listeners\BS\Vehicles\SyncDeleteVehicleListener;
use App\Listeners\BS\Vehicles\SyncVehicleListener;
use App\Listeners\BS\Vehicles\ToggleUseBSListener;
use App\Listeners\Orders\OrderModifyListener;
use App\Listeners\Orders\OrderStatusListener;
use App\Listeners\Orders\OrderUpdateListener;
use App\Listeners\Passport\RevokeOldTokens;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Laravel\Passport\Events\AccessTokenCreated;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        AccessTokenCreated::class => [
            RevokeOldTokens::class,
        ],
        OrderUpdateEvent::class => [
            OrderUpdateListener::class,
        ],
        OrderStatusChanged::class => [
            OrderStatusListener::class,
        ],
        OrderModifyEvent::class => [
            OrderModifyListener::class
        ],
        ToggleUseBSEvent::class => [
            ToggleUseBSListener::class
        ],
        SyncVehicleEvent::class => [
            SyncVehicleListener::class
        ],
        DeleteVehicleEvent::class => [
            SyncDeleteVehicleListener::class
        ],
    ];
}
