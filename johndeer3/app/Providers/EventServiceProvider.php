<?php

namespace App\Providers;

use App\Events\DeactivateFeature;
use App\Events\FcmPushGroup;
use App\Events\UpdateSysTranslations;
use App\Listeners\FcmPushGroupListeners;
use App\Listeners\RemoveDeactivateFeatureListeners;
use App\Listeners\UpdateLangResourceListeners;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        FcmPushGroup::class => [FcmPushGroupListeners::class,],
        DeactivateFeature::class => [RemoveDeactivateFeatureListeners::class,],
        UpdateSysTranslations::class => [UpdateLangResourceListeners::class,],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
