<?php

namespace App\GraphQL\Mutations\User\Auth;

use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Services\Auth\UserPassportService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class UserLogout extends BaseGraphQL
{
    public function __construct(
        protected UserPassportService $passportService
    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return array
     */
    public function __invoke($_, array $args)
    {
        $guard = \Auth::guard(User::GUARD);
        try {
            $this->passportService->logout(
                $guard->user()
            );

            return $this->successResponse(__('auth.user logout'));
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, null, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
