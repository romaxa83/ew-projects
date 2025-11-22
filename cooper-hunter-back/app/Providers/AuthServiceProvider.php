<?php

namespace App\Providers;

use App\Models\Technicians\Technician;
use App\Policies\Technicians\TechnicianPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Technician::class => TechnicianPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
