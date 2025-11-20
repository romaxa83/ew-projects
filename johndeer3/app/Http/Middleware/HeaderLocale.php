<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Illuminate\Support\Facades\Request;

class HeaderLocale
{
    public function handle($request, Closure $next)
    {
        if(Request::header('accept-language') != null){
            $lang = explode('-', Request::header('accept-language'));

            App::setLocale($lang[0]);
        }

        return $next($request);
    }
}
