<?php

namespace App\Http\Controllers\Api\V1\Payments;

use App\Contracts\Payment\ExpectsResponse;
use App\Contracts\Payment\PaymentDriverInterface;
use App\Drivers\PaypalDriver;
use App\Drivers\StripeDriver;
use App\Http\Controllers\Api\ApiController;
use App\Models\Orders\Parts\Order;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends ApiController
{
    public function callbackPaypal(Request $request): JsonResponse
    {
        $id = $request->input('resource.purchase_units.0.reference_id');

        $driver = PaypalDriver::PAYPAL_DRIVER;

        return $this->callback($id, $driver, $request);
    }

    public function callbackStripe(Request $request): JsonResponse
    {
        $id = $request->input('data.object.client_reference_id');

        $driver = StripeDriver::STRIPE_DRIVER;

        return $this->callback($id, $driver, $request);
    }

    public function callback(string $id, $driver, Request $request): JsonResponse
    {
        $order = $this->getOrder($id);

        $driver = $this->getPaymentDriver($order, $driver);

        if ($driver->handleServerRequest($request)) {
            if ($driver instanceof ExpectsResponse) {
                return $driver->successResponse($order, $request);
            }
        } elseif ($driver instanceof ExpectsResponse) {
            return $driver->failedResponse($order);
        }

        return response()->json();
    }

    protected function getOrder(int $id): ?Order
    {
        $order = Order::query()->where('id', $id)->first();
        if (!$order) {
            logger()->error('Order not found!', compact('id'));

            return null;
        }

        return $order;
    }

    protected function getPaymentDriver(Order $order, string $driver): PaymentDriverInterface|
    ResponseFactory|Response|null
    {
        $paymentMethod = $order->payment_method->value;
        if (!$paymentMethod || $paymentMethod != $driver) {
            logger()->error('Order payment not found or driver is empty!', compact('order', 'driver'));

            return response();
        }

        $driver = $order->makeDriver();

        if (!$driver) {
            logger()->error('Payment driver not defined!', compact('order', 'driver'));

            return response();
        }

        return $driver;
    }
}
