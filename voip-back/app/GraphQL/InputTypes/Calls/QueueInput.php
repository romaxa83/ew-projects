<?php

namespace App\GraphQL\InputTypes\Calls;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class QueueInput extends BaseInputType
{
    public const NAME = 'QueueInputType';

    public function fields(): array
    {
        return [
            'from_name' => [
                'type' => Type::string(),
            ],
            'serial_number' => [
                'type' => Type::string(),
            ],
            'case_id' => [
                'type' => Type::string(),
            ],
            'comment' => [
                'type' => Type::string(),
            ],
        ];
    }
}

