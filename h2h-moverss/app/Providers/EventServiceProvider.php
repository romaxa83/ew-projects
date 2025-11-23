<?php

namespace App\Providers;

use App\Listeners\AuditCreatedListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\OrderUpdated;
use App\Listeners\Order\Saving as OrderSaving;
use App\Events\ClientUpdated;
use App\Listeners\Client\Saving as ClientSaving;
use App\Events\ClientPhoneUpdated;
use App\Listeners\Client\PhoneSaving as ClientPhoneSaving;
use App\Events\ClientEmailUpdated;
use App\Listeners\Client\EmailSaving as ClientEmailSaving;
use App\Events\ClientMessengerUpdated;
use App\Listeners\Client\MessengerSaving as ClientMessengerSaving;
use App\Events\ClientNotesUpdated;
use App\Listeners\Client\NotesSaving as ClientNotesSaving;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        OrderUpdated::class => [
            OrderSaving::class,
        ],
        ClientUpdated::class => [
            ClientSaving::class,
        ],
        ClientPhoneUpdated::class => [
            ClientPhoneSaving::class,
        ],
        ClientEmailUpdated::class => [
            ClientEmailSaving::class,
        ],
        ClientMessengerUpdated::class => [
            ClientMessengerSaving::class,
        ],
        ClientNotesUpdated::class => [
            ClientNotesSaving::class,
        ],
        'OwenIt\Auditing\Events\Audited' => [
            AuditCreatedListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
