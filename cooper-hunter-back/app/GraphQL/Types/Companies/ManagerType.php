<?php

namespace App\GraphQL\Types\Companies;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\Manager;
use GraphQL\Type\Definition\Type;

class ManagerType extends BaseType
{
    public const NAME = 'managerType';
    public const MODEL = Manager::class;

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
                'type' => NonNullType::string(),
            ],
            'email' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
