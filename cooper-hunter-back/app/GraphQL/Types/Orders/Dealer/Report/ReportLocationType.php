<?php

namespace App\GraphQL\Types\Orders\Dealer\Report;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class ReportLocationType extends BaseType
{
    public const NAME = 'dealerOrderReportLocationType';

    public function fields(): array
    {
        return [
            'location_name' => [
                'type' => Type::string(),
            ],
            'location_id' => [
                'type' => Type::id(),
            ],
            'total' => [
                'type' => NonNullType::float(),
            ],
            'items' => [
                'type' => ReportLocationItemType::list(),
                'always' => 'items',
                'selectable' => false,
                'is_relation' => false,
            ],
        ];
    }
}
