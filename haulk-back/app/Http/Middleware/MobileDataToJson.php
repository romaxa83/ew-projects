<?php


namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;

class MobileDataToJson
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->merge($request->json()->all());
        return $next($request);
    }
}