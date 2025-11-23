<?php

namespace WezomCms\Core\Http\Middleware;

use Auth;

class RedirectToAdminIfAuthenticated
{
    /**
     * @param $request
     * @param  \Closure  $next
     * @param  string  $guard
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    public function handle($request, \Closure $next, $guard = 'admin')
    {
        if (Auth::guard($guard)->check()) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
