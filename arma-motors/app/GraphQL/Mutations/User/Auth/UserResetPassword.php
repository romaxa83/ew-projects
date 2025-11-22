<?php

namespace App\GraphQL\Mutations\User\Auth;

use App\GraphQL\BaseGraphQL;
use App\Repositories\User\UserRepository;
use App\Services\Sms\SmsVerifyService;
use App\Services\Telegram\TelegramDev;
use App\Services\User\UserService;
use App\ValueObjects\Phone;
use GraphQL\Error\Error;

class UserResetPassword extends BaseGraphQL
{
    public function __construct(
        protected UserService $userService,
        protected UserRepository $userRepository,
        protected SmsVerifyService $smsVerifyService
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
        try {
            $phone = new Phone($args['phone']);
            $password = $args['password'];
            $actionToken = $args['actionToken'];

            $user = $this->userRepository->getByPhone($phone);
            if(!$user){
                throw new \DomainException(__('error.not found user'));
            }

            if($obj = $this->smsVerifyService->getAndCheckByActionToken($actionToken)){
                $this->userService->changePassword($user, $password);
                $obj->delete();
            }

            $user->refresh();

            // @todo dev-telegram
            TelegramDev::info('Пользователю зброшен пароль', $user->name);

            return $this->successResponse(__('message.user.password changed'));
        } catch (\Throwable $e){
            // @todo dev-telegram
            TelegramDev::error(__FILE__, $e, null, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
