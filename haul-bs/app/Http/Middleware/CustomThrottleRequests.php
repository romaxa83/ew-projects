<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests;

class CustomThrottleRequests extends ThrottleRequests
{
    /**
     * Обрабатывает входящий запрос.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int|float|string  $maxAttempts
     * @param  float|string  $decayMinutes
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Http\Exceptions\ThrottleRequestsException
     */
    public function handle($request, Closure $next, $maxAttempts = 250, $decayMinutes = 1, $prefix = '')
    {
        $whitelist = config('app.white_list_ip');
        if (!in_array($request->ip(), $whitelist)) {

            return parent::handle($request, $next, $maxAttempts, $decayMinutes, $prefix);
        }

        return $next($request);
    }
}
