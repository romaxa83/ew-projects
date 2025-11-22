<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;

class WebhookAuth
{
    public function handle($request, Closure $next)
    {
        if($request->header('Authorization') !== config('api.webhook.token')){
            throw new AuthenticationException("Wrong webhook auth-token");
        }

        return $next($request);
    }
}

