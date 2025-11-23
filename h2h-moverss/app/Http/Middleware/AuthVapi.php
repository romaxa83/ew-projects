<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;

class AuthVapi
{
    public function handle($request, Closure $next)
    {
        $token = 'wdpGBBBBlLS9sMjAfeDxgTHplwovTM5ePiKgJcHULnS1X1r33To9CVmNfz8Yb12Q';
        if($request->header('Authorization') !== $token){
            throw new AuthenticationException("Wrong vapi auth-token");
        }

        return $next($request);
    }
}


