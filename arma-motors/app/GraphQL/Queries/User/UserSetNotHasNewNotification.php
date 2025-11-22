<?php

namespace App\GraphQL\Queries\User;

use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Services\Telegram\TelegramDev;
use App\Services\User\UserService;
use GraphQL\Error\Error;

class UserSetNotHasNewNotification extends BaseGraphQL
{
    public function __construct(protected UserService $service){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return array
     */
    public function __invoke($_, array $args): array|string
    {
        /** @var $user User */
        $user = \Auth::guard(User::GUARD)->user();
        try {
            $this->service->changeHasNewNotifications($user, false);

            return $this->successResponse("Change", 200);
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

