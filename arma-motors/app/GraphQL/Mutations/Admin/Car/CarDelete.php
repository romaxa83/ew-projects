<?php

namespace App\GraphQL\Mutations\Admin\Car;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\User\Car;
use App\Repositories\User\CarRepository;
use App\Services\Telegram\TelegramDev;
use App\Services\User\CarService;
use GraphQL\Error\Error;

class CarDelete extends BaseGraphQL
{
    public function __construct(
        protected CarRepository $repository,
        protected CarService $service
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

            $car = $this->repository->trashedFindByID($args['id']);
            $number = $car->number;

            $this->service->forceDelete($car);

            // @todo dev-telegram
            TelegramDev::info("Админ удалил авто из архива ({$number}) ", $user->name);

            return $this->successResponse(__('message.car deleted'));

        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

