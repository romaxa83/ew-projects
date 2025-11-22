<?php

namespace App\GraphQL\Types\Companies;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\Corporation;
use GraphQL\Type\Definition\Type;

class CorporationType extends BaseType
{
    public const NAME = 'corporationType';
    public const MODEL = Corporation::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
            ],
            'name' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
