<?php

namespace App\GraphQL\InputTypes\Schedules;

use App\GraphQL\Types\BaseInputType;
use GraphQL\Type\Definition\Type;

class AdditionDayInput extends BaseInputType
{
    public const NAME = 'AdditionDayInputType';

    public function fields(): array
    {
        return [
            'start_at' => [
                'type' => Type::string(),
                'description' => "формат - Y-m-d"
            ],
            'end_at' => [
                'type' => Type::string(),
                'description' => "формат - Y-m-d"
            ],
        ];
    }
}
