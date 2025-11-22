<?php

namespace App\GraphQL\Mutations\Admin\Car;

use App\Events\User\SendCarDataToAA;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\User\Car;
use App\Repositories\User\CarRepository;
use App\Services\Telegram\TelegramDev;
use App\Services\User\CarService;
use GraphQL\Error\Error;

class CarModerate extends BaseGraphQL
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
     * @return Car
     */
    public function __invoke($_, array $args): Car
    {
        /** @var $user Admin */
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $car = $this->service->toggleModerateStatus(
                $this->repository->findByID($args['id'])
            );

            // отправляем запрос на создание авто в AA если у пользователя есть uuid (соответственно он есть в системе АА)
            if(Car::statusModerate($args['status']) && $car->user->uuid && null == $car->uuid){
                event(new SendCarDataToAA($car));
            }

            // @todo dev-telegram
            TelegramDev::info("Админ промодерировал авто ({$car->number}) ", $user->name);

            return $car;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

