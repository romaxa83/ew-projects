<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

/**
 * @OA\Info(
 *     title="C&H VOIP API documentation",
 *     version="1.0.0",
 *     @OA\Contact(
 *         name="",
 *         email=""
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 * @OA\Tag(
 *     name="Employees",
 *     description="Employee and relative entity and action",
 * )
 * @OA\Tag(
 *     name="Calls",
 *     description="Call and relative entity and action",
 * )
 * @OA\SecurityScheme(
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     securityScheme="Basic"
 * )
 */

class ApiController extends Controller
{
    public static function successJsonMessage($message, $code = Response::HTTP_OK)
    {
        return response()->json([
            'data' => $message,
            'success' => true
        ], $code);
    }

    public static function errorJsonMessage($message, $code = Response::HTTP_INTERNAL_SERVER_ERROR)
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
            'data' => $message,
            'success' => false
        ], $code);
    }
}
