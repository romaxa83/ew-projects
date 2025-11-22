<?php

namespace App\GraphQL\Mutations\Support;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Repositories\Support\MessageRepository;
use App\Services\Support\MessageService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class SupportMessageDelete extends BaseGraphQL
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
     * @return array
     */
    public function __invoke($_, array $args):  array
    {
        /** @var $user Admin */
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
           $this->service->delete(
                $this->repository->findByID($args['id'])
            );

            // @todo dev-telegram
            TelegramDev::info("Админ удалил обращение в тех. поддержку", $user->name);

            return $this->successResponse(__('message.support message deleted'));
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

