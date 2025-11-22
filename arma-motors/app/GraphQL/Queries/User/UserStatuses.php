<?php

namespace App\GraphQL\Queries\User;

use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Services\Telegram\TelegramDev;

class UserStatuses extends BaseGraphQL
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
            return normalizeSimpleData(User::statusList());
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
