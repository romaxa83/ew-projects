<?php

namespace App\Http\Controllers\Communications;

use App\Http\Controllers\Controller;
use App\Http\Resources\Calls\IncomingCalResource;
use App\Models\Calls\IncomingCall;
use Illuminate\Http\JsonResponse;

class CallController extends Controller
{
    public function __construct()
    {}

    /**
     * test @see \Tests\Feature\Communications\Calls\IncomingListTest
     */
    public function incomingList(): JsonResponse
    {
        try {
            $models = IncomingCall::query()
                ->latest('created_at')
                ->get();

        } catch (\Throwable $e) {
            return $this->responseErrorJson(
                $e->getMessage(),
                $e->getCode(),
            );
        }

        return $this->responseDataJson([
            'success' => true,
            'records' => IncomingCalResource::collection($models),
        ]);
    }
}


