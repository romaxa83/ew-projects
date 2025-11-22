<?php

namespace App\GraphQL\Types\Alerts;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Alerts\AlertObjectTypeEnum;
use App\GraphQL\Types\NonNullType;

class AlertObjectType extends BaseType
{

    public const NAME = 'AlertObjectType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'name' => [
                'type' => AlertObjectTypeEnum::nonNullType(),
            ]
        ];
    }
}
