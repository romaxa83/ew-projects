<?php

namespace App\GraphQL\Queries\Support;

use App\GraphQL\BaseGraphQL;
use App\Models\Support\Message;
use App\Repositories\Support\MessageRepository;
use App\Services\Telegram\TelegramDev;

class MessageCountNew extends BaseGraphQL
{
    public function __construct(protected MessageRepository $repository)
    {}

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
            return [
                "key" => "count",
                "name" => $this->repository->countByStatus(Message::STATUS_DRAFT),
            ];
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
