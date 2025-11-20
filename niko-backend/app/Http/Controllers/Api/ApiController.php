<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use WezomCms\Core\Api\ErrorCode;

class ApiController extends Controller
{

    public function check()
    {
        dd('check');
    }

    protected function successJsonMessage($message, $code = Response::HTTP_OK)
    {
        return response()->json([
            'data' => $message,
            'success' => true
        ], $code);
    }

    protected function errorJsonMessage($message, $code = ErrorCode::UNKNOWN)
    {
        return response()->json([
            'data' => $message,
            'success' => false
        ], $code);
    }
}
