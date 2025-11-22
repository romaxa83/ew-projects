<?php

namespace App\GraphQL\Types\Utilities;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class AppVersionType extends BaseType
{
    public const NAME = 'AppVersionType';

    public function fields(): array
    {
        return [
            'recommended_version' => [
                'type' => NonNullType::string(),
                'description' => 'Current latest app version',
            ],
            'required_version' => [
                'type' => NonNullType::string(),
                'description' => 'Minimal required app version',
            ],
        ];
    }
}