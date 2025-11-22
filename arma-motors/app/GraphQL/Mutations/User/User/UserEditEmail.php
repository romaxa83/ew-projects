<?php

namespace App\GraphQL\Mutations\User\User;

use App\Events\Firebase\FcmPush;
use App\Events\User\EmailConfirm;
use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Models\Verify\EmailVerify;
use App\Services\Email\EmailVerifyService;
use App\Services\Firebase\FcmAction;
use App\Services\Telegram\TelegramDev;
use App\Services\User\UserService;
use App\ValueObjects\Email;
use GraphQL\Error\Error;

class UserEditEmail extends BaseGraphQL
{
    public function __construct(
        protected UserService $userService,
        protected EmailVerifyService $emailVerifyService
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
            if(!isset($args['email'])){
                throw new \InvalidArgumentException(__('validation.must exist', ['attribute' => 'email']));
            }
            \Arr::dot([]);
            $email = filter_var($args['email'], FILTER_VALIDATE_EMAIL)
                ? new Email($args['email'])
                : null;

            $user = $this->userService->editEmail($user, $email);

            if(EmailVerify::userEnabled()){
                $emailVerify = $this->emailVerifyService->create($user, true);
                event(new EmailConfirm($user, $emailVerify));
                event(new FcmPush($user, FcmAction::create(FcmAction::EMAIL_VERIFY, [], $user)));
            }

            // @todo dev-telegram
            TelegramDev::info("Пользователь - ({$user->name}) , email изменен");

            return $user;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

