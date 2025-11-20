<?php

namespace WezomCms\Requests\Helpers;

use WezomCms\Requests\Events\OrderRequest;
use WezomCms\Requests\Events\VerifyCarRequest;
use WezomCms\ServicesOrders\Models\ServicesOrder;
use WezomCms\Users\Models\Car;

class RequestEvents
{
    public static function verifyCar(Car $car)
    {
        if(env('1C_USE', false)){

            $car->load(['user', 'brand', 'model']);

            event(new VerifyCarRequest($car));
        }
    }

    public static function sendOrder(ServicesOrder $order)
    {
        if(env('1C_USE', false)){

            $order->load(['user', 'car', 'group']);

            event(new OrderRequest($order));
        }
    }
}
