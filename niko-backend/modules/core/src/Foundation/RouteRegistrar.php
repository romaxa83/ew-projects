<?php

namespace WezomCms\Core\Foundation;

use Illuminate\Routing\Router;
use LaravelLocalization;
use Route;
use WezomCms\Core\Http\Middleware\SetAdminLocale;

class RouteRegistrar
{
    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $routesAreCached;

    /**
     * RouteRegistrar constructor.
     * @param  string  $prefix
     * @param  string  $name
     */
    public function __construct(string $prefix = '', string $name = 'admin.')
    {
        $this->prefix = $prefix;
        $this->name = $name;
        $this->routesAreCached = app()->routesAreCached();
    }

    /**
     * Register core macros
     */
    public function registerMacros()
    {
        if (!Router::hasMacro('settings')) {
            Router::macro('settings', function ($name, $controller) {
                Route::get($name . '/settings/form', ['uses' => $controller . '@settingsForm', 'as' => $name . '.settings']);

                Route::post(
                    $name . '/settings',
                    ['uses' => $controller . '@updateSettings', 'as' => $name . '.update-settings']
                );
                Route::get(
                    $name . '/delete-settings-file/{id}/{locale?}',
                    ['uses' => $controller . '@deleteSettingsFile', 'as' => $name . '.delete-settings-file']
                )->where('id', '\d+');
            });
        }

        if (!Router::hasMacro('adminResource')) {
            /**
             * @return \WezomCms\Core\Foundation\AdminPendingResourceRegistration
             */
            Route::macro('adminResource', function ($name, $controller, array $options = []) {
                return new AdminPendingResourceRegistration(
                    app()->make(AdminResourceRegistrar::class),
                    $name,
                    $controller,
                    $options
                );
            });
        }
    }

    /**
     * @param  callable|string  $callback  - Callback function or path to file
     */
    public function adminRoutes($callback)
    {
        if ($this->routesAreCached) {
            return;
        }

        Route::prefix($this->prefix)
            ->name($this->name)
            ->middleware(['web', 'auth:admin', SetAdminLocale::class])
            ->group($callback);
    }

    /**
     * @param  callable|string  $callback  - Callback function or path to file
     */
    public function adminRoutesWithoutAuth($callback)
    {
        if ($this->routesAreCached) {
            return;
        }

        Route::prefix($this->prefix)
            ->name($this->name)
            ->middleware(['web', SetAdminLocale::class])
            ->group($callback);
    }

    /**
     * @param  callable|string  $callback  - Callback function or path to file
     */
    public function siteRoutes($callback)
    {
        if ($this->routesAreCached) {
            return;
        }

        Route::prefix(LaravelLocalization::setLocale())
            ->middleware(['web', 'localize', 'localizationRedirect'])
            ->group($callback);
    }

    /**
     * @param  callable|string  $callback  - Callback function or path to file
     */
    public function apiRoutes($callback)
    {
        if (!app()->routesAreCached() && config('cms.core.api.enabled')) {
            if (!is_callable($callback)) {
                $callback = function () use ($callback) {
                    require $callback;
                };
            }

            Route::prefix('api/v' . config('cms.core.api.version'))
                ->middleware([
                    'set.locale',
//                    'auth:api',
                    'api',
                    'bindings'
                ])
                ->group($callback);
        }
    }
}
