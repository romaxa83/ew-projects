<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

/**
 * @OA\Info(
 *     title="Arma-motors mobile API documentation",
 *     version="1.0.0",
 *     @OA\Contact(
 *         name="Rodomanov Roman",
 *         email="rodomanov.r.wezom@gmail.com"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 * @OA\Tag(
 *     name="Dealership",
 *     description="Dealerships",
 * )
 * @OA\Tag(
 *     name="Service",
 *     description="Services",
 * )
 * @OA\Tag(
 *     name="Catalog",
 *     description="Car models and brand catalog",
 * )
 * @OA\Tag(
 *     name="User",
 *     description="User and relative entity and action",
 * )
 * @OA\Tag(
 *     name="Order",
 *     description="Order",
 * )
 * @OA\Server(
 *     description="stage server",
 *     url="https://arma-motors.wezom.agency/api/v1"
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
    protected function successJsonMessage($message, $code = Response::HTTP_OK)
    {
        return response()->json([
            'data' => $message,
            'success' => true
        ], $code);
    }

    protected function errorJsonMessage($message, $code = Response::HTTP_INTERNAL_SERVER_ERROR)
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
