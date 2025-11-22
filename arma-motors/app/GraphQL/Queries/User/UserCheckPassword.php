<?php

namespace App\GraphQL\Queries\User;

use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class UserCheckPassword extends BaseGraphQL
{
    public function __construct(){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return User|string
     */
    public function __invoke($_, array $args): array|string
    {
        $user = \Auth::guard(User::GUARD)->user();
        try {
            $password = $args['password'];

            // @todo dev-telegram
            TelegramDev::info('Запрос на валидность пароля', $user->name);

            if(!password_verify($password, $user->password)){
                throw new \InvalidArgumentException(__('error.not valid user password'), ErrorsCode::BAD_REQUEST);
            }

            return $this->successResponse(__('message.user.password check'));
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
