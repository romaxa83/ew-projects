<?php


namespace App\Http\Middleware;

use Arr;
use App\Models\Language as LanguageModel;
use Closure;
use Config;
use Illuminate\Http\Request;
use Lang;

class Language
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Config::get('languages') && in_array($request->header('App-Language'), Arr::pluck(Config::get('languages'), 'slug'))) {
            $this->setLanguage('', 'Language', $request);
        } elseif (auth('api')->check()) {
            $this->setLanguage('api', 'Language', $request);
        }
        
        return $next($request);
    }

    /**
     * @param string $guard
     * @param string $param
     * @param $request
     */
    private function setLanguage(string $guard, string $param, $request)
    {
        $requestLanguage = $request->header('App-Language');
        $clientsLanguage = auth($guard)->check() ? auth($guard)->user()->language : null;

        if ($requestLanguage) {
            Lang::setLocale($requestLanguage);
            Config::set('app.locale', $requestLanguage);
            $request->headers->set($param, $requestLanguage);
        } elseif ($clientsLanguage) {
            Lang::setLocale($clientsLanguage);
            Config::set('app.locale', $clientsLanguage);
            $request->headers->set($param, $clientsLanguage);
        } else {
            $defaultLanguage = LanguageModel::whereDefault(true)->firstOrFail();
            Lang::setLocale($defaultLanguage->slug);
            Config::set('app.locale', $defaultLanguage->slug);
            $request->headers->set($param, $defaultLanguage->slug);
        }
    }

}