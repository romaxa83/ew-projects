<?php

namespace App\GraphQL\Types\Schedules;

use App\GraphQL\Types\BaseType;
use App\Models\Schedules\Schedule;

class ScheduleType extends BaseType
{
    public const NAME = 'ScheduleType';
    public const MODEL = Schedule::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'days' => [
                    'is_relation' => true,
                    'type' => DayType::list(),
                ],
                'additional_days' => [
                    'alias' => 'additionalDays',
                    'is_relation' => true,
                    'type' => AdditionDay::list(),
                ],
            ]
        );
    }
}


