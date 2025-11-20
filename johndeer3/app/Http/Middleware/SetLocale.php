<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        if(Request::header('Content-Language') != null){
            if(App\Models\Translate::checkLanguage(Request::header('Content-Language'))){
                App::setLocale(Request::header('Content-Language'));

                return $next($request);
            }
        }

        if($user = Auth::user()){
            if(App::getLocale() != $user->lang) {
                App::setLocale($user->lang);
            }
        }

        return $next($request);
    }
}
