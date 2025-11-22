<?php

namespace App\Http\Middleware;

use App;
use App\Models\Admin\Admin;
use App\Models\Localization\Language;
use App\Models\User\User;
use Closure;
use Illuminate\Support\Facades\Request;

class SetLocale
{
    public function handle($request, Closure $next)
    {

//        $user = \Auth::guard(Admin::GUARD)->user();
//        if(null == $user){
//            $user = \Auth::guard(User::GUARD)->user();
//        }
//
//        if($user && $user->lang){
//            App::setLocale($user->lang);
//        } elseif (Request::header('Content-Language') != null) {
//            App::setLocale(Request::header('Content-Language'));
//        } elseif ($lang = Language::default()->first()) {
//            App::setLocale($lang->slug);
//        }

        return $next($request);
    }
}

