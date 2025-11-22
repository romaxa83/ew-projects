<?php

namespace App\Http\Middleware;

use App\Models\Users\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckBodyShopAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user(User::GUARD);

        if (!$user || !$user->isBodyShopUser()) {
            return response(
                [
                    'errors' => [
                        [
                            'title' => trans('Company not found.'),
                            'status' => Response::HTTP_FORBIDDEN,
                        ]
                    ]
                ],
                Response::HTTP_FORBIDDEN
            );
        }

        return $next($request);
    }
}
