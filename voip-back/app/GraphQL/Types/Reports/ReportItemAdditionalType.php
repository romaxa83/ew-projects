<?php

namespace App\GraphQL\Types\Reports;

use App\GraphQL\Types\BaseType;
use GraphQL\Type\Definition\Type;

class ReportItemAdditionalType extends BaseType
{
    public const NAME = 'ReportItemAdditionalType';

    public function fields(): array
    {
        return [
            'total_calls' => [
                'type' => Type::int(),
            ],
            'total_dropped' => [
                'type' => Type::int(),
            ],
            'total_wait' => [
                'type' => Type::int(),
            ],
            'total_time' => [
                'type' => Type::int(),
            ],
        ];
    }
}

