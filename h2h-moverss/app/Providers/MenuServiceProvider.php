<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\View\Composers\MenuComposer;

class MenuServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer(
            ['layouts.app.*','layouts.order.record.tab-overview.order', 'layouts.app'], MenuComposer::class
        );
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(MenuComposer::class);
    }
}
