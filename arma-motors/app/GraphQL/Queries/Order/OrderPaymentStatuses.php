<?php

namespace App\GraphQL\Queries\Order;

use App\GraphQL\BaseGraphQL;
use App\Services\Telegram\TelegramDev;
use App\Types\Order\PaymentStatus;
use App\Types\Order\Status;

class OrderPaymentStatuses extends BaseGraphQL
{
    /**
     *
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return array
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): array
    {
        try {
            return normalizeSimpleData(PaymentStatus::list());
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
