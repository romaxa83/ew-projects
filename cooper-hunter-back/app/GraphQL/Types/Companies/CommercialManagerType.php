<?php

namespace App\GraphQL\Types\Companies;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\CommercialManager;
use GraphQL\Type\Definition\Type;

class CommercialManagerType extends BaseType
{
    public const NAME = 'commercialManagerType';
    public const MODEL = CommercialManager::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
            ],
            'name' => [
                'type' => NonNullType::string(),
            ],
            'phone' => [
                'type' => Type::string(),
            ],
            'email' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
