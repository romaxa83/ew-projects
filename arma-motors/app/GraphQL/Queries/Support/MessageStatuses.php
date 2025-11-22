<?php

namespace App\GraphQL\Queries\Support;

use App\GraphQL\BaseGraphQL;
use App\Models\Support\Message;
use App\Services\Telegram\TelegramDev;

class MessageStatuses extends BaseGraphQL
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
            return normalizeSimpleData(Message::statusList());
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

