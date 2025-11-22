<?php

namespace App\Http\Middleware;

use App\Http\Exceptions\HttpTranslatedException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Guest
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        foreach (empty($guards) ? [null] : $guards as $guard) {
            if (Auth::guard($guard)->check()) {
                throw new HttpTranslatedException(__('Authenticated'), Response::HTTP_FORBIDDEN);
            }
        }

        return $next($request);
    }
}
