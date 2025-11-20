<?php

namespace App\GraphQL\Types\Reports;

use App\GraphQL\Types\BaseType;
use GraphQL\Type\Definition\Type;

class ReportPauseItemAdditionalType extends BaseType
{
    public const NAME = 'ReportPauseItemAdditionalType';

    public function fields(): array
    {
        return [
            'pause' => [
                'type' => Type::int(),
            ],
            'total_pause_time' => [
                'type' => Type::int(),
            ],
        ];
    }
}
