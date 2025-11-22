<?php


namespace App\Http\Middleware;


use App\Services\Language\LanguageService;
use Closure;
use Illuminate\Http\Request;

class ConfigLanguages
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
        resolve(LanguageService::class)->load();

        return $next($request);
    }
}
