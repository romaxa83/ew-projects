<?php

namespace App\GraphQL\Mutations\Order;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Repositories\Order\OrderRepository;
use App\Services\Order\OrderService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class OrderDelete extends BaseGraphQL
{
    public function __construct(
        protected OrderRepository $repository,
        protected OrderService $service
    ){}

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
        /** @var $user Admin */
        $user = \Auth::guard(Admin::GUARD)->user();
        try {

            $this->service->delete(
                $this->repository->FindByID($args['id'])
            );

            // @todo dev-telegram
            TelegramDev::info("Админ удалил заявку в архив ({$args['id']}) ", $user->name);

            return $this->successResponse(__('message.order.remove to archive'));
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

