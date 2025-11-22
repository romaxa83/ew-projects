<?php

namespace App\GraphQL\Types\Security;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Security\IpAccess;
use GraphQL\Type\Definition\Type;

class IpAccessType extends BaseType
{
    public const NAME = 'IpAccessType';
    public const MODEL = IpAccess::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'address' => [
                    'type' => NonNullType::string(),
                ],
                'description' => [
                    'type' => Type::string(),
                ],
                'active' => [
                    'type' => NonNullType::boolean(),
                ],
            ]
        );
    }
}
