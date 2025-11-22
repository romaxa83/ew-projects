<?php

namespace App\Http\Controllers;

use App\Models\OneC\Moderator;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    protected function success(array|string|null $data = null, int $code = 200): JsonResponse
    {
        return response()->json(
            [
                'success' => true,
                'message' => is_string($data) ? $data : 'Success',
                'data' => is_array($data) ? $data : []
            ],
            $code
        );
    }

    protected function user(): Moderator|Authenticatable|null
    {
        return Auth::guard(Moderator::GUARD)->user();
    }
}
