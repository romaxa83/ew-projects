<?php

namespace App\GraphQL\Types\Schedules;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Schedules\DayEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Schedules\WorkDay;
use GraphQL\Type\Definition\Type;

class DayType extends BaseType
{
    public const NAME = 'DayType';
    public const MODEL = WorkDay::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'name' => [
                'type' => DayEnumType::Type(),
            ],
            'start_work_time' => [
                'type' => Type::string(),
            ],
            'end_work_time' => [
                'type' => Type::string(),
            ],
            'sort' => [
                'type' => Type::int(),
            ],
            'active' => [
                'type' => Type::boolean(),
            ],
        ];
    }
}

