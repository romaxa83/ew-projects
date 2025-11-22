<?php

namespace App\GraphQL\Mutations\Admin\CarOrderStatus;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\User\OrderCar\OrderCarStatus;
use App\Repositories\User\CarOrder\UserCarOrderStatusRepository;
use App\Services\Telegram\TelegramDev;
use App\Services\User\CarOrderService;
use GraphQL\Error\Error;

class CarOrderStatusStateEdit extends BaseGraphQL
{
    public function __construct(
        protected CarOrderService $service,
        protected UserCarOrderStatusRepository $repository,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return OrderCarStatus
     */
    public function __invoke($_, array $args): OrderCarStatus
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $model = $this->service->editStatus(
                $this->repository->findByID($args['id'], ['statusName']),
                $args
            );

            // @todo dev-telegram
            TelegramDev::info("EDIT: состояния статуса [{$model->id}] ", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}


