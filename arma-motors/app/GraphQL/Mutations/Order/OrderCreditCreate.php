<?php

namespace App\GraphQL\Mutations\Order;

use App\DTO\Order\OrderCreditDTO;
use App\GraphQL\BaseGraphQL;
use App\Helpers\Logger\OrderLogger;
use App\Models\Order\Order;
use App\Models\User\User;
use App\Repositories\Catalog\Service\ServiceRepository;
use App\Services\Order\OrderService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class OrderCreditCreate extends BaseGraphQL
{
    public function __construct(
        protected OrderService $service,
        protected ServiceRepository $serviceRepository
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
        /** @var $user User */
        $user = \Auth::guard(User::GUARD)->user();
        try {
            $service = $this->serviceRepository->getCreditService();
            $args['serviceId'] = $service->id;
            $dto = OrderCreditDTO::byArgs($args);

            $order = $this->service->createCredit($dto, $user);

            OrderLogger::info('CREATE [credit] by data', $args);

            TelegramDev::info("Пользователь создал заявку на страховку", $user->name);

            return $order;
        } catch (\Throwable $e) {
            OrderLogger::error("CREATE order has a error [{$e->getMessage()}]", $e->getTrace());
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
