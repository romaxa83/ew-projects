<?php

namespace WezomCms\Orders\Http\Controllers\Site;

use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\SiteController;
use WezomCms\Orders\Contracts\ExpectsResponse;
use WezomCms\Orders\Enums\PayedModes;
use WezomCms\Orders\Events\AutoPayedOrder;
use WezomCms\Orders\Models\Order;

class OrderPaymentController extends SiteController
{
    /**
     * @param $id
     * @param $driver
     * @param  Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function __invoke($id, $driver, Request $request)
    {
        $order = Order::find($id);
        if (!$order) {
            logger()->error('Order not found!', compact('id', 'driver'));

            return response();
        }

        $payment = $order->payment;
        if (!$payment || !$payment->driver || $payment->driver != $driver) {
            logger()->error('Order payment not found or driver is empty!', compact('id', 'driver', 'payment'));

            return response();
        }

        $driver = $payment->makeDriver($order);
        if (!$driver) {
            logger()->error('Payment driver not defined!', compact('id', 'driver', 'payment'));

            return response();
        }

        if ($driver->handleServerRequest($request)) {
            $order->payed = true;
            $order->payed_mode = PayedModes::AUTO;
            $order->payed_at = now();
            $order->save();

            event(new AutoPayedOrder($order));

            if ($driver instanceof ExpectsResponse) {
                return $driver->successResponse($order);
            }
        } elseif ($driver instanceof ExpectsResponse) {
            return $driver->failedResponse($order);
        }

        return response();
    }
}
