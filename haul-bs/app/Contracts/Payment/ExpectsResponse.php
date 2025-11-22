<?php

namespace App\Contracts\Payment;

use App\Models\Orders\Parts\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ExpectsResponse
{
    public function successResponse(Order $order, Request $request): JsonResponse;

    public function failedResponse(Order $order): JsonResponse;
}
