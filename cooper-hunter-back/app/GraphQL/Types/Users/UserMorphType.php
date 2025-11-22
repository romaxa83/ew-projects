<?php

namespace App\GraphQL\Types\Users;

use App\Contracts\Roles\HasGuardUser;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Commercial\RDPAccountType;
use App\GraphQL\Types\Enums\Users\UserMorphTypeEnum;
use App\GraphQL\Types\NonNullType;

class UserMorphType extends BaseType
{
    public const NAME = 'UserMorphType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'resolve' => fn(HasGuardUser $user) => $user->getId(),
            ],
            'type' => [
                'type' => UserMorphTypeEnum::nonNullType(),
                'resolve' => fn(HasGuardUser $user) => $user->getMorphType(),
                'selectable' => false,
            ],
            'name' => [
                'type' => NonNullType::string(),
                'resolve' => fn(HasGuardUser $user) => $user->getName(),
                'selectable' => false,
                'always' => ['first_name', 'last_name'],
            ],
            'email' => [
                'type' => NonNullType::string(),
                'resolve' => fn(HasGuardUser $user) => $user->getEmail(),
            ],
        ];
    }
}
