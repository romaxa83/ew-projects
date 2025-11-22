<?php

namespace App\GraphQL\Types\Orders\Dealer\Report;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\Dealer\OrderFileFromOnecType;
use GraphQL\Type\Definition\Type;

class ReportType extends BaseType
{
    public const NAME = 'dealerOrderReportType';

    public function fields(): array
    {
        return [
            'company_name' => [
                'type' => Type::string(),
            ],
            'company_id' => [
                'type' => Type::id(),
            ],
            'total' => [
                'type' => NonNullType::float(),
            ],
            'locations' => [
                'type' => ReportLocationType::list(),
                'always' => 'locations',
                'selectable' => false,
                'is_relation' => false,
            ],
        ];
    }
}

