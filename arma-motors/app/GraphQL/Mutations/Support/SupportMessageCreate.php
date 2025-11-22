<?php

namespace App\GraphQL\Mutations\Support;

use App\DTO\Support\MessageDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Services\Support\MessageService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class SupportMessageCreate extends BaseGraphQL
{
    public function __construct(
        protected MessageService $service,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return array
     */
    public function __invoke($_, array $args): array
    {
        /** @var $user User */
        $user = \Auth::guard(User::GUARD)->user();
        try {

            $this->service->create(
                MessageDTO::byArgs($args)
            );

            // @todo dev-telegram
            TelegramDev::info("Пришло обращение в тех. поддержку", $user->name ?? null);

            return $this->successResponse(__('message.support message accept'));
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
