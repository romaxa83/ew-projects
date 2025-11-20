<?php

namespace App\GraphQL\Types\Reports;

use App\GraphQL\Types\BaseType;
use GraphQL\Type\Definition\Type;

class ReportAdditionalType extends BaseType
{
    public const NAME = 'ReportAdditionalType';

    public function fields(): array
    {
        return [
            'total_calls' => [
                'type' => Type::int(),
            ],
            'total_answer_calls' => [
                'type' => Type::int(),
            ],
            'total_dropped_calls' => [
                'type' => Type::int(),
            ],
            'total_transfer_calls' => [
                'type' => Type::int(),
            ],
            'total_wait' => [
                'type' => Type::int(),
            ],
            'total_time' => [
                'type' => Type::int(),
            ],
            'total_pause' => [
                'type' => Type::int(),
            ],
            'total_pause_time' => [
                'type' => Type::int(),
            ],
        ];
    }
}
