<?php

namespace App\Services\Orders\BS;

use App\Dto\Orders\BS\OrderPaymentDto;
use App\Foundations\Modules\History\Services\OrderBSHistoryService;
use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\Payment;
use App\Services\Events\EventService;

class OrderPaymentService
{
    public function __construct()
    {}

    public function add(Order $order, OrderPaymentDto $dto): Payment
    {
        return make_transaction(function () use ($dto, $order){

            $model = new Payment();
            $model->order_id = $order->id;
            $model->amount = $dto->amount;
            $model->payment_date = $dto->paymentDate;
            $model->payment_method = $dto->paymentMethod;
            $model->notes = $dto->notes;
            $model->reference_number = $dto->referenceNumber;
            $model->save();

            $order->refresh();
            $order->setAmounts();
            $order->resolvePaidStatus();

            EventService::bsOrder($order)
                ->custom(OrderBSHistoryService::ACTION_CREATE_PAYMENT)
                ->initiator(auth_user())
                ->setHistory([
                    'payment' => $model
                ])
                ->exec()
            ;

            return $model;
        });
    }

    public function delete(Order $order, Payment $payment):bool
    {
        return make_transaction(function () use ($payment, $order){

            $old = $payment;

            $res = $payment->delete();

            $order->refresh();
            $order->setAmounts();
            $order->resolvePaidStatus();

            EventService::bsOrder($order)
                ->custom(OrderBSHistoryService::ACTION_DELETE_PAYMENT)
                ->initiator(auth_user())
                ->setHistory([
                    'payment' => $old
                ])
                ->exec()
            ;

            return $res;
        });
    }
}
