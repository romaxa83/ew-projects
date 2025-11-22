<?php

namespace App\Http\Middleware;

use App\Models\Admins\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CKFinderAuth
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->has('access_token')) {
            $request->headers->set('Authorization', 'Bearer ' . $request->get('access_token'));
        }

        config(
            [
                'ckfinder.authentication' => function () {
                    return Auth::guard(Admin::GUARD)->check();
                }
            ]
        );

        return $next($request);
    }
}
