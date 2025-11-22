<?php

namespace App\Providers;

use App\Services\Localizations\Export\ExportFromDBToSystemFile;
use App\Services\Localizations\Export\ExportTranslation;
use App\Services\Localizations\Import\ImportFromSystemFileToDB;
use App\Services\Localizations\Import\ImportTranslation;
use App\Services\Localizations\LocalizationService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(LocalizationService::class, LocalizationService::class);

        $this->app->singleton('localization', function (Application $app) {
            return $app->make(LocalizationService::class);
        });

        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->singleton(ImportTranslation::class, function (Application $app) {
            return $app->make(ImportFromSystemFileToDB::class);
        });
        $this->app->singleton(ExportTranslation::class, function (Application $app) {
            return $app->make(ExportFromDBToSystemFile::class);
        });
    }

    public function boot(): void
    {
        $this->allowAllToSuperAdminRole();
    }

    protected function allowAllToSuperAdminRole(): void
    {
        Gate::before(
            function ($admin, $ability) {
                return $admin->hasRole(config('permission.roles.super_admin'));
            }
        );
    }
}
