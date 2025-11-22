<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class BsAuth
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->header('Authorization') !== config('app.sync_bs_token')){
            throw new AuthenticationException("Wrong bs auth-token");
        }

        return $next($request);
    }
}
