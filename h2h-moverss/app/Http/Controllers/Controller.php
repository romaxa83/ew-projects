<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function responseErrorJson(
        string $msg = "Oops, something wrong",
        int|string $code = JsonResponse::HTTP_INTERNAL_SERVER_ERROR
    ): JsonResponse
    {
        if($code == '0') {
            $code = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
        }

        return response()
            ->json([
                'success' => false,
                'msg' => $msg
            ])
            ->setStatusCode($code)
            ;
    }

    public function responseDataJson(
        array $data = [],
        $code = JsonResponse::HTTP_OK
    ): JsonResponse
    {
        return response()
            ->json($data)
            ->setStatusCode($code)
            ;
    }
}
