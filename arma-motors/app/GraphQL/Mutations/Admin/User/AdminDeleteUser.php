<?php

namespace App\GraphQL\Mutations\Admin\User;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Repositories\User\UserRepository;
use App\Services\Telegram\TelegramDev;
use App\Services\User\UserService;
use GraphQL\Error\Error;

class AdminDeleteUser extends BaseGraphQL
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
     * @return array
     */
    public function __invoke($_, array $args): array
    {
        $admin = \Auth::guard(Admin::GUARD)->user();
        try {
            $user = $this->userRepository->findByID($args['id']);
            $userName = $user->name;

            $this->userService->delete($user);

            // @todo dev-telegram
            TelegramDev::info("Админ удалил пользователя - ({$userName})", $admin->name);

            return $this->successResponse(__('message.user deleted'));
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $admin->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
