<?php

namespace App\GraphQL\InputTypes\Projects\Systems;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class ProjectSystemCreateInput extends BaseInputType
{
    public const NAME = 'ProjectSystemCreateInput';

    public function fields(): array
    {
        return [
            'name' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string'],
            ],
            'description' => [
                'type' => Type::string(),
                'rules' => ['sometimes', 'nullable', 'string'],
            ],
            'units' => [
                'type' => ProjectSystemUnitInput::list(),
            ],
        ];
    }
}
