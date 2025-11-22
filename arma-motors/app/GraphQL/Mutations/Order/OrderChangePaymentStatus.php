<?php

namespace App\GraphQL\Mutations\Order;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Order\Order;
use App\Repositories\Order\OrderRepository;
use App\Services\Order\OrderService;
use App\Services\Telegram\TelegramDev;
use App\Types\Order\PaymentStatus;
use GraphQL\Error\Error;

class OrderChangePaymentStatus extends BaseGraphQL
{
    public function __construct(
        protected OrderService $service,
        protected OrderRepository $repository,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Order
     */
    public function __invoke($_, array $args): Order
    {
        /** @var $user Admin */
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $order = $this->repository->findByID($args['id'], ['service']);
            $status = PaymentStatus::create($args['status']);

            $order = $this->service->changePaymentStatus(
                $order, $status
            );

            TelegramDev::info("📊 Админ сменил статус оплаты заявки c \"{$order->payment_status}\" на \"{$args['status']}\"", $user->name);

            return $order;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

