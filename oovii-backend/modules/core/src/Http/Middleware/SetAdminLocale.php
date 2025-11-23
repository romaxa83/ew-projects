<?php

namespace WezomCms\Core\Http\Middleware;

use Illuminate\Http\Request;

class SetAdminLocale
{
    /**
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $locale = $request->cookie('admin_locale');

        $locale = array_key_exists($locale, config('cms.core.translations.admin.locales', []))
            ? $locale
            : config('cms.core.translations.admin.default');

        \App::setLocale($locale);

        if (! array_key_exists($locale, app('locales'))) {
            // Switch translations to default front locale.
            config()->set('translatable.locale', \LaravelLocalization::getDefaultLocale());
        }

        return $next($request);
    }
}
