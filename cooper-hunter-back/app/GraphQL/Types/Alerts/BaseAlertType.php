<?php

namespace App\GraphQL\Types\Alerts;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class BaseAlertType extends BaseType
{
    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'description' => [
                'type' => NonNullType::string(),
            ],
            'object' => [
                'type' => AlertObjectType::nonNullType(),
            ],
            'created_at' => [
                'type' => NonNullType::int(),
                'description' => 'Time in unix',
            ],
            'is_read' => [
                'type' => NonNullType::boolean(),
            ]
        ];
    }
}
