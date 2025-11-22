<?php

namespace App\GraphQL\Types\SupportRequests;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\SupportRequests\SupportRequestSubscriptionActionTypeEnum;
use App\GraphQL\Types\NonNullType;

class SupportRequestSubscriptionType extends BaseType
{
    public const NAME = 'SupportRequestSubscriptionType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'description' => 'Support request ID'
            ],
            'action' => [
                'type' => SupportRequestSubscriptionActionTypeEnum::nonNullType(),
            ]
        ];
    }
}
