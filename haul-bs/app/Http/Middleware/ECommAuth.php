<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;

class ECommAuth
{
    public function handle($request, Closure $next)
    {
        if($request->header('Authorization') !== config('api.e_comm.token')){
            throw new AuthenticationException("Wrong e-comm auth-token");
        }

        return $next($request);
    }
}

