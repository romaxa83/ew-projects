<?php


namespace App\Http\Middleware;


use Closure;
use Config;
use Illuminate\Http\Request;

class WebCrmPanel
{
    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->header('Admin-Panel') == true) {
            Config::set('mobile', false);
        } else {
            Config::set('mobile', true);
        }
        return $next($request);
    }
}