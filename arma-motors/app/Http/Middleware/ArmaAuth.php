<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Illuminate\Support\Facades\Request;

class ArmaAuth
{
    public function handle($request, Closure $next)
    {
        if(Request::header('Authorization') == null){
            return response()->json([
                'success' => false,
                'data' => 'Missing authorization header'
            ], App\Exceptions\ErrorsCode::NOT_AUTH);
        }

        $token = last(explode(' ', Request::header('Authorization')));
        $handler = app(App\Services\Token\ApiToken::class);

        if(!$handler->checkToken($token)){
            return response()->json([
                'success' => false,
                'data' => 'Bad authorization token'
            ], App\Exceptions\ErrorsCode::NOT_AUTH);
        }

        return $next($request);
    }
}
