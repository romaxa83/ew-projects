<?php

namespace WezomCms\Orders\Contracts;

use Illuminate\Http\Response;
use WezomCms\Orders\Models\Order;

interface ExpectsResponse
{
    /**
     * @param  Order  $order
     * @return Response
     */
    public function successResponse(Order $order): Response;

    /**
     * @param  Order  $order
     * @return Response
     */
    public function failedResponse(Order $order): Response;
}
