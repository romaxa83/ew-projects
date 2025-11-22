<?php

namespace App\GraphQL\Middlewares\Localization;

use App\Models\Admins\Admin;
use App\Models\BaseAuthenticatable;
use App\Models\Languageable;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Closure;
use Core\Traits\Auth\AuthGuardsTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;

class SystemLangSetterMiddleware
{
    use AuthGuardsTrait;

    public function handle(Request $request, Closure $next)
    {
        if (
            ($lang = $request->headers->get(config('localization.header')))
            && app('localization')->hasLang($lang)
        ) {
            $this->setLocale($request, $lang);

            return $next($request);
        }

        if (($auth = $this->getCurrentAuth()) && ($lang = $auth->getLangSlug())) {
            $this->setLocale($request, $lang);
        }

        return $next($request);
    }

    protected function setLocale(Request $request, string $lang): void
    {
        Lang::setLocale($lang);

        Config::set('app.locale', $lang);

        $request->headers->set('Language', $lang);
    }

    protected function getCurrentAuth(): BaseAuthenticatable|Authenticatable|Languageable|null
    {
        if ($this->authCheck(User::GUARD)) {
            return $this->user(User::GUARD);
        }

        if ($this->authCheck(Technician::GUARD)) {
            return $this->user(Technician::GUARD);
        }

        if ($this->authCheck(Admin::GUARD)) {
            return $this->user(Admin::GUARD);
        }

        return null;
    }
}
