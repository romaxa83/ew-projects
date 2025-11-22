<?php

namespace App\GraphQL\Queries\User;

use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class UserHasNewNotification extends BaseGraphQL
{
    public function __construct(){}

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
        $user = \Auth::guard(User::GUARD)->user();
        try {
            if($user->hasNewNotification()){
                return $this->successResponse("OK", 200);
            }

            return $this->errorResponse("False", 400);
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
