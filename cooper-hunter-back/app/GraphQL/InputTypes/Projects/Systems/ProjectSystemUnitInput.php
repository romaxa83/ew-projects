<?php

namespace App\GraphQL\InputTypes\Projects\Systems;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class ProjectSystemUnitInput extends BaseInputType
{
    public const NAME = 'ProjectSystemUnitInput';

    public function fields(): array
    {
        return [
            'product_id' => [
                'type' => NonNullType::id(),
            ],
            'serial_number' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
