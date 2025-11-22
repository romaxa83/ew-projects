<?php

namespace App\GraphQL\Mutations\User\User;

use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Repositories\User\UserRepository;
use App\Services\Telegram\TelegramDev;
use App\Services\User\UserService;
use GraphQL\Error\Error;

class UserEditPassword extends BaseGraphQL
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
        /** @var $user User */
        $user = \Auth::guard(User::GUARD)->user();
        try {
//            $phone = new Phone($args['phone']);
            $password = $args['password'];
//            $oldPassword = $args['oldPassword'];

//            $user = $this->userRepository->getByPhone($phone);
//            if(!$user){
//                throw new \DomainException(__('error.not found user'));
//            }
//
//            if(!password_verify($oldPassword, $user->password)){
//                throw new \InvalidArgumentException(__('error.wrong old password'));
//            }

            $this->userService->changePassword($user, $password);

            $user->refresh();

            // @todo dev-telegram
            TelegramDev::info('Пользователь сменил пароль', $user->name);

            return $this->successResponse(__('message.user.password changed'));

        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

