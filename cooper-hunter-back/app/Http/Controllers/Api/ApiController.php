<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiController extends Controller
{
    public static function responseSuccess(string|array $msg, $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'data' => $msg,
            'success' => true
        ], $code);
    }

    public static function responseError(string $msg, $code = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        if(!is_numeric($code)){
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
        } else {
            if((int)$code < 100 || (int)$code > 600){
                $code = Response::HTTP_INTERNAL_SERVER_ERROR;
            }
        }

        if($code == 0) {
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return response()->json([
            'data' => $msg,
            'success' => false
        ], $code);
    }
}
