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

class OrderAttachAdmin extends BaseGraphQL
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
            $order = $this->repository->findByID($args['id'], ['service']);

            // @todo временное роешени , когда АА реализует свою часть, раскоментировать, и вернуть тест
            // Tests\Feature\Mutations\Order\OrderAttachAdminTest::fail_order_not_related_system
//            if($order->isRelateToAA()){
//                throw new \Exception(__('error.order.order not support action'), ErrorsCode::BAD_REQUEST);
//            }

            $order = $this->service->attachAdmin(
                $order, $args['adminId'] ?: null
            );

            TelegramDev::info("Админ привязал админа ({$args['adminId']}) к заявке ({$args['id']})", $user->name);

            return $order;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
