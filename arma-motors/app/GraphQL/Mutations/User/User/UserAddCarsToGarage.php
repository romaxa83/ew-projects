<?php

namespace App\GraphQL\Mutations\User\User;

use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Services\Telegram\TelegramDev;
use App\Services\User\CarService;
use GraphQL\Error\Error;

class UserAddCarsToGarage extends BaseGraphQL
{
    public function __construct(
        protected CarService $carService
    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return User
     */
    public function __invoke($_, array $args): User
    {
        /** @var $user User*/
        $user = \Auth::guard(User::GUARD)->user();
        try {

            $ids = $args['carIds'];

            $this->carService->addCarsToGarage($ids);


            // @todo dev-telegram
            $data = serialize($ids);
            TelegramDev::info("Пользователь добавил авто в гараж [{$data}]", $user->name);

            $user->refresh();

            return $user;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
