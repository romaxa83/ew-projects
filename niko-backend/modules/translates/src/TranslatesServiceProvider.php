<?php

namespace WezomCms\Translates;

use WezomCms\Core\BaseServiceProvider;

class TranslatesServiceProvider extends BaseServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Application booting.
     */
    public function boot()
    {
        \RouteRegistrar::apiRoutes($this->root('routes/api.php'));

        parent::boot();
    }
}

