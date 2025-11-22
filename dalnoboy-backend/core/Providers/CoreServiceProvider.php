<?php

declare(strict_types=1);

namespace Core\Providers;

use App\Models\Admins\Admin;
use App\Providers\TelescopeServiceProvider;
use App\Services\Localizations\LocaleService;
use App\Services\Localizations\LocalizationService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        $this->app->register(TelescopeServiceProvider::class);

        $this->app->bind(LocalizationService::class, LocalizationService::class);

        $this->app->singleton(
            'localization',
            fn(Application $app) => $app->make(LocalizationService::class)
        );

        $this->app->singleton(
            'locales',
            fn(Application $app) => $app->make(LocaleService::class)
        );

        $this->registerMacro();
    }

    protected function registerMacro(): void
    {
    }

    public function boot(): void
    {
        $this->allowAllToSuperAdminRole();
    }

    protected function allowAllToSuperAdminRole(): void
    {
        Gate::before(
            fn($user, $ability) => $user instanceof Admin
                && $user->hasRole(config('permission.roles.super_admin')) ? true : null
        );
    }
}
