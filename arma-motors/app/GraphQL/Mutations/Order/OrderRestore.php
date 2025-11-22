<?php

namespace App\GraphQL\Mutations\Order;

use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Order\Order;
use App\Repositories\Order\OrderRepository;
use App\Services\Order\OrderService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class OrderRestore extends BaseGraphQL
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
            /** @var $order Order */
            $order = $this->repository->trashedFindByID($args['id']);

            $order = $this->service->restore($order);

            TelegramDev::info("Восстановлена заявка ({$order->id})", $user->name);

            return $order;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
