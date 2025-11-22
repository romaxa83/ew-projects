<?php

namespace App\GraphQL\Mutations\Support;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Support\Message;
use App\Repositories\Support\MessageRepository;
use App\Services\Support\MessageService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class SupportMessageChangeStatus extends BaseGraphQL
{
    public function __construct(
        protected MessageService $service,
        protected MessageRepository $repository,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Message
     */
    public function __invoke($_, array $args): Message
    {
        /** @var $user Admin */
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $model = $this->service->changeStatus(
                $this->repository->findByID($args['id']),
                $args['status']
            );

            // @todo dev-telegram
            TelegramDev::info("Админ сменил статус обращение в тех. поддержку", $user->name );

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
