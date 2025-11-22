<?php


namespace App\Http\Middleware;

use Arr;
use App\Models\Language as LanguageModel;
use Closure;
use Config;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Lang;

class FlespiAuth
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
        if($request->header('Authorization') !== config('flespi.webhook.auth_token')){
            throw new AuthenticationException("Wrong flespi webhook auth-token");
        }

        return $next($request);
    }
}
