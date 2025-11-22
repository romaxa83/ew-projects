<?php

namespace App\GraphQL\Queries\User;

use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Repositories\User\FcmNotificationRepository;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class UserNotifications extends BaseGraphQL
{
    public function __construct(
        protected FcmNotificationRepository $notificationRepository
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return User|string
     */
    public function __invoke($_, array $args)
    {
        $user = \Auth::guard(User::GUARD)->user();
        try {

            return $this->successResponse(__('message.user.password check'));
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
