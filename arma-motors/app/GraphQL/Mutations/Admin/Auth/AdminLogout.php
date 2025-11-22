<?php

namespace App\GraphQL\Mutations\Admin\Auth;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Services\Auth\AdminPassportService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class AdminLogout extends BaseGraphQL
{
    public function __construct(
        protected AdminPassportService $passportService
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
        $guard = \Auth::guard(Admin::GUARD);
        try {
            $this->passportService->logout(
                $guard->user()
            );

            return $this->successResponse(__('auth.admin logout'));
        } catch (\Throwable $e) {
            // @todo dev-telegram
            TelegramDev::error(__FILE__, $e, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
