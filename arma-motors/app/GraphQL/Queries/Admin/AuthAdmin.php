<?php

namespace App\GraphQL\Queries\Admin;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class AuthAdmin extends BaseGraphQL
{
    /**
     * Return information about current user
     *
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return Admin
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): Admin
    {
        /** @var $admin Admin */
        $admin = \Auth::guard(Admin::GUARD)->user();
        try {
            if (null === $admin) {
                throw new Error(__('auth.not auth'));
            }

            $admin->isSuperAdmin();

            return $admin;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $admin->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
