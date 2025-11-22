<?php

namespace App\GraphQL\Mutations\User\User;

use App\DTO\User\UserDTO;
use App\DTO\User\UserEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Services\Telegram\TelegramDev;
use App\Services\User\UserService;
use GraphQL\Error\Error;

class UserEdit extends BaseGraphQL
{
    public function __construct(
        protected UserService $userService
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
        /** @var $user User */
        $user = \Auth::guard(User::GUARD)->user();
        try {

            $user = $this->userService->update(UserEditDTO::byArgs($args), $user);

            // @todo dev-telegram
//            TelegramDev::info("Пользователь - ({$user->name}) , отредактирован");

            return $user;
        } catch (\Throwable $e) {
//            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
