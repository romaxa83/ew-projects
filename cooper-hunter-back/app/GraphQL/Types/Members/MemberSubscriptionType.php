<?php

namespace App\GraphQL\Types\Members;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Users\MemberMorphTypeEnum;
use App\GraphQL\Types\NonNullType;

class MemberSubscriptionType extends BaseType
{
    public const NAME = 'MemberSubscriptionType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'description' => 'Member ID',
            ],
            'type' => [
                'type' => MemberMorphTypeEnum::nonNullType(),
                'description' => 'Member type',
            ],
        ];
    }
}
