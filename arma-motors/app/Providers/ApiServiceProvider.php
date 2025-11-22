<?php

namespace App\Providers;

use App\Services\Token\ApiToken;
use App\Services\Token\SimpleApiToken;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    public $bindings = [
        ApiToken::class => SimpleApiToken::class
    ];
}
