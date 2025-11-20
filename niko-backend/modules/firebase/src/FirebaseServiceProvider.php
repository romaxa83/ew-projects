<?php

namespace WezomCms\Firebase;

use WezomCms\Core\BaseServiceProvider;
use WezomCms\Firebase\Events\FcmNotificationEvent;
use WezomCms\Firebase\Listeners\FcmNotificationListener;

class FirebaseServiceProvider extends BaseServiceProvider
{
    protected $listen = [
        FcmNotificationEvent::class => [
            FcmNotificationListener::class,
        ],
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(){}

    /**
     * Application booting.
     */
    public function boot()
    {
        \RouteRegistrar::apiRoutes($this->root('routes/api.php'));

        parent::boot();
    }
}

