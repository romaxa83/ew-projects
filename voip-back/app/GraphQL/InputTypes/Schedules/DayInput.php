<?php

namespace App\GraphQL\InputTypes\Schedules;

use App\GraphQL\Types\BaseInputType;
use GraphQL\Type\Definition\Type;

class DayInput extends BaseInputType
{
    public const NAME = 'DayInputType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
            ],
            'start_work_time' => [
                'type' => Type::string(),
                'description' => "Начала рабочего дня, формат - 8:00, 8:30, 15:00 ..."
            ],
            'end_work_time' => [
                'type' => Type::string(),
                'description' => "Конец рабочего дня, формат - 8:00, 8:30, 15:00 ..."
            ],
            'active' => [
                'type' => Type::boolean(),
            ],
        ];
    }
}
