<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Auth;

class Authenticate extends Middleware
{
    public function handle($request, Closure|\Closure $next, ...$guards)
    {
        $user = Auth::user();

        if ($user) {
            if (!$user->active) {
                Auth::logout();
                abort(403);
            }

            if (Auth::viaRemember() || !$request->session()->get('division')) {
                $loginController = new LoginController();

                $loginController->authenticated($request, $user);
            }
        }

        return parent::handle($request, $next, ...$guards);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route('login');
        }
    }
}
