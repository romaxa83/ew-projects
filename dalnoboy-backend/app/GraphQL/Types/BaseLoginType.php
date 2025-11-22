<?php

namespace App\GraphQL\Types;

abstract class BaseLoginType extends BaseType
{
    public function fields(): array
    {
        return [
            'token_type' => [
                'type' => NonNullType::string(),
            ],
            'access_token' => [
                'type' => NonNullType::string(),
            ],
            'refresh_token' => [
                'type' => NonNullType::string(),
            ],
            'access_token_expires_in' => [
                'type' => NonNullType::int(),
                'resolve' => fn(array $tokenData) => $tokenData['expires_in'],
            ],
            'refresh_token_expires_in' => [
                'type' => NonNullType::int(),
                'resolve' => fn(array $tokenData) => getRefreshTokenData($tokenData['refresh_token'], true)
                    ->expiresAt
                    ->diffInSeconds()
            ],
        ];
    }
}
