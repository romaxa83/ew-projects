<?php


namespace App\Http\Middleware;


use App\Models\Users\User;
use Auth;
use Closure;
use Illuminate\Http\Request;

class RevokeTokenIfUserInactive
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard('api')->check() && Auth::guard('api')->user()) {
            /** @var User $user */
            $user = Auth::guard('api')->user();
            if ($user->token() && !$user->isActive()) {
                $user->token()->revoke();
            }
        }
        return $next($request);
    }
}