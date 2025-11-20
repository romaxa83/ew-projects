<?php

namespace App\Providers;

use App\Events\Departments\DepartmentCreatedEvent;
use App\Events\Employees\EmployeeCreatedEvent;
use App\Events\Employees\EmployeeUpdatedEvent;
use App\IPTelephony;
use App\Listeners\Employees\SendCredentialsListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        EmployeeCreatedEvent::class => [
            SendCredentialsListener::class,
            IPTelephony\Listeners\Subscriber\SubscriberInsertListener::class,
            IPTelephony\Listeners\QueueMember\QueueMemberInsertListener::class,
        ],
        EmployeeUpdatedEvent::class => [
            IPTelephony\Listeners\Subscriber\SubscriberUpdateOrInsertListener::class,
            IPTelephony\Listeners\QueueMember\QueueMemberUpdateOrInsertListener::class
        ],
        IPTelephony\Events\Subscriber\SubscriberDeleteEvent::class => [
            IPTelephony\Listeners\Subscriber\SubscriberDeleteListeners::class,
            IPTelephony\Listeners\QueueMember\QueueMemberDeleteListener::class,
        ],
        IPTelephony\Events\Subscriber\SubscriberUpdateOrCreateEvent::class => [
            IPTelephony\Listeners\Subscriber\SubscriberUpdateOrInsertListener::class,
            IPTelephony\Listeners\QueueMember\QueueMemberUpdateOrInsertListener::class
        ],
        DepartmentCreatedEvent::class => [
            IPTelephony\Listeners\Queue\QueueInsertListener::class
        ],
        IPTelephony\Events\Queue\QueueUpdateOrCreateEvent::class => [
            IPTelephony\Listeners\Queue\QueueUpdateOrInsertListener::class
        ],
        IPTelephony\Events\Queue\QueueDeleteEvent::class => [
            IPTelephony\Listeners\Queue\QueueDeleteListener::class
        ],
        IPTelephony\Events\Queue\QueueUpdateMusicEvent::class => [
            IPTelephony\Listeners\Queue\QueueUpdateMusicListener::class
        ],
        IPTelephony\Events\Queue\QueueDeleteMusicEvent::class => [
            IPTelephony\Listeners\Queue\QueueDeleteMusicListener::class
        ],
        IPTelephony\Events\QueueMember\QueueMemberUpdateEvent::class => [
            IPTelephony\Listeners\QueueMember\QueueMemberUpdateOrInsertListener::class
        ],
        IPTelephony\Events\QueueMember\QueueMemberPausedEvent::class => [
            IPTelephony\Listeners\QueueMember\QueueMemberPausedListener::class
        ],
        IPTelephony\Events\QueueMember\QueueMemberUpdateNameEvent::class => [
            IPTelephony\Listeners\QueueMember\QueueMemberUpdateNameListener::class
        ],
        IPTelephony\Events\QueueMember\QueueMemberDeleteAndInsertEvent::class => [
            IPTelephony\Listeners\QueueMember\QueueMemberDeleteAndInsertListener::class
        ],
    ];

    public function listens(): array
    {
        return parent::listens()
            + config('events.default');
    }
}
