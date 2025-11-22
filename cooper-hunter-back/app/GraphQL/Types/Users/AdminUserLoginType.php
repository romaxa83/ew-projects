<?php

namespace App\GraphQL\Types\Users;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class AdminUserLoginType extends BaseType
{
    public const NAME = 'AdminUserLoginType';

    public function fields(): array
    {
        return [
            'access_token' => [
                'type' => NonNullType::string(),
            ],
            'expires_in' => [
                'type' => NonNullType::int()
            ],
            'token_type' => [
                'type' => NonNullType::string()
            ]
        ];
    }
}
