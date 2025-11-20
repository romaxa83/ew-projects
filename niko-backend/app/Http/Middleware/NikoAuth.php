<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Illuminate\Support\Facades\Request;

class NikoAuth
{
    public function handle($request, Closure $next)
    {
        if(Request::header('Authorization') == null){
            return response()->json([
                'success' => false,
                'message' => 'Missing authorization header'
            ], 401);
        }

        $auth = explode(':', base64_decode(last(explode(' ', Request::header('Authorization')))));

        if(empty($auth) && (count($auth) != 2) && ($auth[0] != env('1C_LOGIN')) && ($auth[1] != env('1C_PASSWORD'))){
            return response()->json([
                'success' => false,
                'message' => 'Bad authorization token'
            ], 401);
        }

        if(($auth[0] != env('1C_LOGIN')) || ($auth[1] != env('1C_PASSWORD'))){
            return response()->json([
                'success' => false,
                'message' => 'Bad authorization token'
            ], 401);
        }


        return $next($request);
    }
}
