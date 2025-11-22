<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SwaggerFix
{
    public function handle(Request $request, Closure $next)
    {
        //check if we have an X-Authorization header present
        if ($auth = $request->header('X-Authorization')) {
            $request->headers->set('Authorization', $auth);
        }
        return $next($request);
    }
}