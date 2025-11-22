<?php

namespace App\GraphQL\Mutations\User\User;

use App\GraphQL\BaseGraphQL;
use App\Models\User\Car;
use App\Models\User\User;
use App\Services\Telegram\TelegramDev;
use App\Services\User\CarService;
use GraphQL\Error\Error;

class UserDeleteCar extends BaseGraphQL
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
     * @return array
     */
    public function __invoke($_, array $args): array
    {
        /** @var $user User*/
        $user = \Auth::guard(User::GUARD)->user();
        try {

            if($args['reason'] === Car::REASON_OTHER && !isset($args['comment'])){
                throw new \InvalidArgumentException(__('error.required comment for delete car', ['reason' => $args['reason']]));
            }

            $this->carService->deleteCarFromUser(
                $user,
                $args['id'],
                $args['reason'],
                $args['comment'] ?? null
            );

            // @todo dev-telegram
            TelegramDev::info("Пользователь, удалил авто ({$args['id']}), причина - {$args['reason']}", $user->name);

            return $this->successResponse(__('message.user.delete car'));

        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
