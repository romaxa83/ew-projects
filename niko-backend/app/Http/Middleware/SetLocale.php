<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Illuminate\Support\Facades\Request;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        if(Request::header('Content-Language') != null){
            App::setLocale(Request::header('Content-Language'));
        }

        return $next($request);
    }
}
