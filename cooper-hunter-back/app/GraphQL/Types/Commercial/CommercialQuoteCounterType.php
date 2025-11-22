<?php

namespace App\GraphQL\Types\Commercial;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class CommercialQuoteCounterType extends BaseType
{
    public const NAME = 'CommercialQuoteCounterType';

    public function fields(): array
    {
        return [
            'pending' => [
                'type' => NonNullType::int(),
            ],
            'done' => [
                'type' => NonNullType::int(),
            ],
            'final' => [
                'type' => NonNullType::int(),
            ],
            'total' => [
                'type' => NonNullType::int(),
            ]
        ];
    }
}

