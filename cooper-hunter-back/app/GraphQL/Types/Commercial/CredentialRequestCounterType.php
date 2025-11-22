<?php

namespace App\GraphQL\Types\Commercial;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class CredentialRequestCounterType extends BaseType
{
    public const NAME = 'CredentialRequestCounterType';

    public function fields(): array
    {
        return [
            'new' => [
                'type' => NonNullType::int(),
            ],
            'approved' => [
                'type' => NonNullType::int(),
            ],
            'denied' => [
                'type' => NonNullType::int(),
            ],
            'total' => [
                'type' => NonNullType::int(),
            ]
        ];
    }
}

