<?php

namespace App\Providers;

use App\Models\Report\Report;
use App\Models\User\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        Passport::routes();
        $this->registerPolicies();
        $this->registerReportPolicies();
    }

    private function registerReportPolicies(): void
    {
        Gate::define('create-report', function (User $user) {
            return $user->isPS();
        });

        Gate::define('update-report', function (User $user, Report $report) {
            return $user->isPS() && ($user->id === $report->user_id);
        });

        Gate::define('show-list-user', function (User $user) {
            return $user->isAdmin() || $user->isSM() ;
        });

        Gate::define('show-report', function (User $user, Report $report) {

//            if($user->isAdmin() || $user->isPSS()){
//                return true;
//            }
//
//            if($user->isPS() || ($report->isOwner($user) || $report->isOwnerDealer($user))){
//                return true;
//            }

            return true;
        });
    }
}
