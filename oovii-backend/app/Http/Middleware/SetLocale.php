<?php

namespace App\Http\Middleware;

use App;
use Auth;
use Closure;
use Illuminate\Support\Facades\Request;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if($user && $user->lang){
            App::setLocale($user->lang);
        } elseif (!is_null(Request::header('Content-Language'))) {
            App::setLocale(Request::header('Content-Language'));
        } elseif ($lang = config('cms.core.translations.app.default')) {
            App::setLocale($lang);
        }

        return $next($request);
    }
}
