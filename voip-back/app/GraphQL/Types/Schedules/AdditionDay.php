<?php

namespace App\GraphQL\Types\Schedules;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Schedules\AdditionsDay;
use GraphQL\Type\Definition\Type;

class AdditionDay extends BaseType
{
    public const NAME = 'AdditionDayType';
    public const MODEL = AdditionsDay::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'start_at' => [
                'type' => Type::string(),
            ],
            'end_at' => [
                'type' => Type::string(),
            ],
        ];
    }
}
