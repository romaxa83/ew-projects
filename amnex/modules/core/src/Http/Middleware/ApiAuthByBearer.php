<?php

namespace Wezom\Core\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiAuthByBearer
{
    /**
     * @return mixed|JsonResponse
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->bearerToken();

        if (!$token || !$this->validateToken($token)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }

    private function validateToken($token): bool
    {
        return $token && $token === config('api.token');
    }
}
