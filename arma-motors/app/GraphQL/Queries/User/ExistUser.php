<?php

namespace App\GraphQL\Queries\User;

use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Repositories\User\UserRepository;
use App\Services\Telegram\TelegramDev;
use App\ValueObjects\Phone;
use GraphQL\Error\Error;

class ExistUser extends BaseGraphQL
{
    public function __construct(
        protected UserRepository $userRepository
    ){}

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
        try {
            $phone = new Phone($args['phone']);

            // @todo dev-telegram
            TelegramDev::info('Запрос на существование пользователя по телефону - ');

            if($this->userRepository->getByPhoneWithTrashed($phone)){
                return $this->successResponse(__('message.user.user exist'));
            }

            return $this->errorResponse(__('error.not found user'));

        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

