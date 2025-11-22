<?php

namespace App\GraphQL\Types\Members;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Users\MemberMorphTypeEnum;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class MemberType extends BaseType
{
    public const NAME = 'MemberType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'email' => [
                'type' => NonNullType::string(),
            ],
            'phone' => [
                'type' => Type::string(),
            ],
            'first_name' => [
                'type' => NonNullType::string(),
            ],
            'last_name' => [
                'type' => Type::string(),
            ],
            'type' => [
                'type' => MemberMorphTypeEnum::nonNullType()
            ],
            'created_at' => [
                'type' => NonNullType::string()
            ]
        ];
    }
}
