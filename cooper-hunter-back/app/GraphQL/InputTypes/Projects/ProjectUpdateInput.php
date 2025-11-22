<?php

namespace App\GraphQL\InputTypes\Projects;

use App\GraphQL\InputTypes\Projects\Systems\ProjectSystemUpdateInput;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class ProjectUpdateInput extends BaseInputType
{
    public const NAME = 'ProjectUpdateInput';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'name' => [
                'type' => NonNullType::string(),
            ],
            'systems' => [
                'type' => ProjectSystemUpdateInput::list(),
            ],
        ];
    }
}
