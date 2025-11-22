<?php

namespace App\GraphQL\Types\Alerts;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class AlertCounterType extends BaseType
{

    public const NAME = 'AlertCounterType';

    public function fields(): array
    {
        return [
            'not_read' => [
                'type' => NonNullType::int(),
            ],
            'total' => [
                'type' => NonNullType::int(),
            ]
        ];
    }
}
