<?php

namespace App\Providers;

use App\Repositories\User\UserRepository;
use Illuminate\Support\ServiceProvider;
use PaginateRoute;
use Illuminate\Database\Query\Builder;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        PaginateRoute::registerMacros();

        Builder::macro('notAdmin', function(): Builder{
            /** @var $this Builder */
            return $this->whereNotIn('id',
                app(UserRepository::class)->getAdmins()->pluck('id')->toArray()
            );
        });
    }
}
