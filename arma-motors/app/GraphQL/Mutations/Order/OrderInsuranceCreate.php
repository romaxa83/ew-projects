<?php

namespace App\GraphQL\Mutations\Order;

use App\DTO\Order\OrderInsuranceDTO;
use App\GraphQL\BaseGraphQL;
use App\Helpers\Logger\OrderLogger;
use App\Models\Order\Order;
use App\Models\User\User;
use App\Services\Order\OrderService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class OrderInsuranceCreate extends BaseGraphQL
{
    public function __construct(protected OrderService $service)
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
            $dto = OrderInsuranceDTO::byArgs($args);
            $order = $this->service->createInsurance($dto, $user);

            OrderLogger::info('CREATE [insurance] by data', $args);

            TelegramDev::info("Пользователь создал заявку на страховку", $user->name);

            return $order;
        } catch (\Throwable $e) {
            OrderLogger::error("CREATE order has a error [{$e->getMessage()}]", $e->getTrace());
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
