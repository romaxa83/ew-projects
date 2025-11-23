<?php

namespace WezomCms\Orders\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Orders\Enums\PayedModes;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderPaymentInformation;
use WezomCms\Orders\Services\SdekService;

class PayBoxController extends ApiController
{
    public function __construct(private SdekService $SDEKService)
    {
        parent::__construct();
    }

    public function check(Request $request): JsonResponse
    {
        Log::channel('pay-box')->info('Pay-box check:');
        Log::channel('pay-box')->info(json_encode($request->all()));

        return self::successJsonMessage([]);
    }

    public function result(Request $request): Response
    {
        $paymentInfo = OrderPaymentInformation::query()
            ->where('order_ids', $request->get('pg_order_id'))
            ->first();

        if ($paymentInfo) {
            $paymentCurrency = $request->get('pg_currency');
            $paymentSum = $request->get('pg_amount');
            $paymentResult = $request->get('pg_result');

            if ($paymentResult
                && $paymentSum >= $paymentInfo->getTotalSum()
                && $paymentCurrency === Order::getCurrencyIsoCodeAttribute()) {
                foreach ($paymentInfo->orders as $order) {
                    $order->setPaid(PayedModes::AUTO)->save();
                }
            }

            $paymentInfo->setPaymentDataFromRequest($request);
        }

        return response()->xml([
            'pg_status' => 'ok',
            'pg_description' => __('cms-orders::site.Order has been paid'),
            'pg_salt' => $request->get('pg_salt'),
            'pg_sig' => $request->get('pg_sig'),
        ]);
    }
}
