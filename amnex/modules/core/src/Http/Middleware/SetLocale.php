<?php

namespace Wezom\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (
            ($lang = $request->headers->get(config('translations.header')))
            && app('localization')->hasLang($lang)
        ) {
            app()->setLocale($lang);

            $request->headers->set('Content-Language', $lang);
        }

        return $next($request);
    }
}
