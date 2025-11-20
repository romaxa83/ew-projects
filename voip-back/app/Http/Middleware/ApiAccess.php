<?php

namespace App\Http\Middleware;

use App;
use App\Services\Token\ApiToken;
use Closure;
use Illuminate\Support\Facades\Request;

class ApiAccess
{
    public function handle($request, Closure $next)
    {
        if(Request::header('Authorization') == null){
            return response()->json([
                'success' => false,
                'data' => 'Missing authorization header'
            ], 401);
        }

        $token = last(explode(' ', Request::header('Authorization')));
        /** @var $handler ApiToken */
        $handler = app(ApiToken::class);

        if(!$handler->checkToken($token)){
            return response()->json([
                'success' => false,
                'data' => 'Bad authorization token'
            ], 401);
        }

        return $next($request);
    }
}

