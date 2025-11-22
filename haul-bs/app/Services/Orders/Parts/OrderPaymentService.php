<?php

namespace App\Services\Orders\Parts;

use App\Dto\Orders\BS\OrderPaymentDto;
use App\Foundations\Enums\LogKeyEnum;
use App\Foundations\Modules\History\Services\OrderBSHistoryService;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Models\Orders\Parts\Order;
use App\Models\Orders\Parts\Payment;
use App\Notifications\Orders\Parts\PaymentLink;
use App\Services\Events\EventService;
use App\Services\Payments\PaymentService;
use Illuminate\Support\Facades\Notification;

class OrderPaymentService
{
    public function __construct(
        protected PaymentService $paymentService,
    )
    {}

    public function add(Order $order, OrderPaymentDto $dto): Payment
    {
        return make_transaction(function () use ($dto, $order){

            $model = new Payment();
            $model->order_id = $order->id;
            $model->amount = $dto->amount;
            $model->payment_at = $dto->paymentDate;
            $model->payment_method = $dto->paymentMethod;
            $model->notes = $dto->notes;
            $model->save();

            $order->refresh();
            $order->setAmounts();
            $order->resolvePaidStatus();

            EventService::partsOrder($order)
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

            EventService::partsOrder($order)
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

    public function sendLink(Order $order): void
    {
        $email = $order->customer
            ? $order->customer->email->getValue()
            : $order->ecommerce_client->email->getValue();

        $name = $order->customer
            ? $order->customer->full_name
            : $order->ecommerce_client->getFullNameAttribute();

        try {
            $link = $this->paymentService->getPaymentLink($order);

            if(!$link){
                throw new \Exception('No link for a payment');
            }

            Notification::route('mail', $email)
                ->notify(new PaymentLink($name, $link));

            EventService::partsOrder($order)
                ->initiator(auth_user())
                ->setHistory([
                    'email' => $email,
                    'name' => $name,
                ])
                ->custom(OrderPartsHistoryService::ACTION_SEND_PAYMENT_LINK)
                ->exec()
            ;

        } catch (\Throwable $e) {
            logger_info(LogKeyEnum::SendEmail() . ' - ' . $e->getMessage());
        }
    }
}
