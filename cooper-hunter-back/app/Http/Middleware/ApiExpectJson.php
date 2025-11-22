<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiExpectJson
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
        if ($request->expectsJson() && $request->acceptsJson()) {
            return $next($request);
        }

        return response()
            ->json(
                [
                    'message' => 'This request only allows JSON content.'
                ],
                Response::HTTP_BAD_REQUEST
            );
    }
}
