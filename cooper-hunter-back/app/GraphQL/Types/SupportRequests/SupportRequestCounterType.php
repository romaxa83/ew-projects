<?php

namespace App\GraphQL\Types\SupportRequests;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class SupportRequestCounterType extends BaseType
{
    public const NAME = 'SupportRequestCounterType';

    public function fields(): array
    {
        return [
            'new' => [
                'type' => NonNullType::int(),
                'description' => 'Used only backoffice'
            ],
            'new_messages' => [
                'type' => NonNullType::int(),
            ]
        ];
    }


}
