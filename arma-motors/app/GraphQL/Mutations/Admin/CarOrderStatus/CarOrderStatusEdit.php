<?php

namespace App\GraphQL\Mutations\Admin\CarOrderStatus;

use App\DTO\User\CarOrderStatus\CarOrderStatusEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\User\OrderCar\OrderStatus;
use App\Repositories\User\CarOrder\CarOrderStatusRepository;
use App\Services\Telegram\TelegramDev;
use App\Services\User\CarOrderStatusService;
use GraphQL\Error\Error;

class CarOrderStatusEdit extends BaseGraphQL
{
    public function __construct(
        protected CarOrderStatusService $service,
        protected CarOrderStatusRepository $repository,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return OrderStatus
     */
    public function __invoke($_, array $args): OrderStatus
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $model = $this->service->edit(
                CarOrderStatusEditDTO::byArgs($args),
                $this->repository->findByID($args['id'])
            );

            // @todo dev-telegram
            TelegramDev::info("EDIT: cтатус машина в заказе ", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}


