<?php

namespace App\GraphQL\Types\Orders\Dealer\Report;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class ReportLocationItemType extends BaseType
{
    public const NAME = 'dealerOrderReportLocationItemType';

    public function fields(): array
    {
        return [
            'po' => [
                'type' => NonNullType::string(),
            ],
            'qty' => [
                'type' => NonNullType::int(),
            ],
            'price' => [
                'type' => NonNullType::float(),
            ],
            'desc' => [
                'type' => Type::string(),
            ],
            'product_title' => [
                'type' => NonNullType::string(),
            ],
            'date' => [
                'type' => NonNullType::string(),
            ],
            'total' => [
                'type' => NonNullType::float(),
            ],
        ];
    }
}
