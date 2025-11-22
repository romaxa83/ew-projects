<?php

namespace App\GraphQL\Mutations\Admin\User;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\User\User;
use App\Repositories\User\UserRepository;
use App\Services\Telegram\TelegramDev;
use App\Services\User\UserService;
use GraphQL\Error\Error;

class AdminRestoreUser extends BaseGraphQL
{
    public function __construct(
        protected UserService $userService,
        protected UserRepository $userRepository
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
        $admin = \Auth::guard(Admin::GUARD)->user();
        try {
            $user = $this->userService->restore(
                $this->userRepository->trashedFindByID($args['id'])
            );

            // @todo dev-telegram
            TelegramDev::info("Админ восстановил пользователя - ({$user->name})", $admin->name);

            return $user;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $admin->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
