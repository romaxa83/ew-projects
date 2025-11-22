<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailDeliveryLog
{

    private const AUTH_HEADER = 'Authorization';

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->header(self::AUTH_HEADER) !== config('emaillog.log_token')) {
            return response(
                [
                    'errors' => [
                        [
                            'title' => 'Forbidden',
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
