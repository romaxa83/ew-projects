<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FileBrowserAuthorization
{
    public function handle(Request $request, Closure $next)
    {
        $parameterName = config('filebrowser.auth_token_parameter');

        if ($request->has($parameterName)) {
            $token = $request->get($parameterName);

            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}
