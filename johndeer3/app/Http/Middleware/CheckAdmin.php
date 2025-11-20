<?php

namespace App\Http\Middleware;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Closure;

class CheckAdmin
{
    public function handle($request,Closure $next)
    {
        if(Auth::check() && Auth::user()->isAdmin()){
            return $next($request);
        }

        return response()->json([
            'data' => __('message.no_access'),
            'success' => false
        ], Response::HTTP_FORBIDDEN);
    }
}
