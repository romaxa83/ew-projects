<?php

namespace App\Providers;

use App\Events\Companies\CompanyCreatedEvent;
use App\Events\Users\UserRegisteredEvent;
use App\Listeners\Users\OwnerRegisteredSetRoleListener;
use App\Listeners\Users\UserRegisteredListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserRegisteredEvent::class => [
            UserRegisteredListener::class,
            OwnerRegisteredSetRoleListener::class,
        ],

        CompanyCreatedEvent::class => [
        ],
    ];

    public function listens(): array
    {
        return parent::listens()
            + config('events.default');
    }
}
