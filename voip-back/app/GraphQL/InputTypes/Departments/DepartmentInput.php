<?php

namespace App\GraphQL\InputTypes\Departments;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class DepartmentInput extends BaseInputType
{
    public const NAME = 'DepartmentInputType';

    public function fields(): array
    {
        return [
            'name' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
