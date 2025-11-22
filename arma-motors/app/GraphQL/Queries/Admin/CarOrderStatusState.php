<?php

namespace App\GraphQL\Queries\Admin;

use App\GraphQL\BaseGraphQL;
use App\Models\User\OrderCar\OrderCarStatus;
use App\Services\Telegram\TelegramDev;

class CarOrderStatusState extends BaseGraphQL
{
    /**
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return array
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): array
    {
        try {
            return normalizeSimpleData(OrderCarStatus::stateList());
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
