<?php

namespace App\GraphQL\Types;

use App\Models\Admins\Admin;
use App\Models\Dealers\Dealer;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Laravel\Passport\Passport;

abstract class BaseLoginType extends BaseType
{
    public function fields(): array
    {
        return [
            'token_type' => [
                'type' => NonNullType::string(),
            ],
            /** @see BaseLoginType::resolveAccessExpiresInField() */
            'access_expires_in' => [
                'type' => NonNullType::int(),
            ],
            /** @see BaseLoginType::resolveRefreshExpiresInField() */
            'refresh_expires_in' => [
                'type' => NonNullType::int(),
            ],
            'access_token' => [
                'type' => NonNullType::string(),
            ],
            'refresh_token' => [
                'type' => NonNullType::string(),
            ],
            'member_guard' => [
                /** @see BaseLoginType::resolveMemberGuardField() */
                'type' => NonNullType::string(),
                'selectable' => false,
                'description' => 'Guards available: '.implode(' | ', [Admin::GUARD, User::GUARD, Technician::GUARD, Dealer::GUARD])
            ],
        ];
    }

    protected function resolveAccessExpiresInField(array $root): int
    {
        return $root['expires_in'];
    }

    protected function resolveRefreshExpiresInField(): int
    {
        return dateIntervalToSeconds(
            Passport::$refreshTokensExpireIn
        );
    }

    protected function resolveMemberGuardField(array $root): string
    {
        return $root['member_guard'];
    }
}
