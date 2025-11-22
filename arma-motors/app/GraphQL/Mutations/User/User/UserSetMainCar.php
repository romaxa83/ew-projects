<?php

namespace App\GraphQL\Mutations\User\User;

use App\GraphQL\BaseGraphQL;
use App\Models\User\Car;
use App\Models\User\User;
use App\Services\Telegram\TelegramDev;
use App\Services\User\CarService;
use App\Services\User\UserService;
use GraphQL\Error\Error;

class UserSetMainCar extends BaseGraphQL
{
    public function __construct(
        protected UserService $userService,
        protected CarService $carService,
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
        /** @var $user User */
        $user = \Auth::guard(User::GUARD)->user();
        try {

            $user->load(['cars']);
            $carId = $args['id'];

            $car = $this->carService->setMain($user->cars, $carId);
            // @todo dev-telegram
            TelegramDev::info("Пользователь - ({$user->name}) , установил главное авто ({$car->number})");

            return $car;

        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
