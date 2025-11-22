<?php

namespace App\GraphQL\Types\Auth\Sms;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

abstract class BaseSmsToken extends BaseType
{
    public function fields(): array
    {
        return [
            'token' => [
                'type' => NonNullType::string(),
            ],
            'expires_at' => [
                'type' => NonNullType::int(),
                'description' => 'value in timestamp'
            ],
        ];
    }
}
