<?php

namespace App\GraphQL\InputTypes\Projects;

use App\GraphQL\InputTypes\Projects\Systems\ProjectSystemCreateInput;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class ProjectCreateInput extends BaseInputType
{
    public const NAME = 'ProjectCreateInput';

    public function fields(): array
    {
        return [
            'name' => [
                'type' => NonNullType::string(),
            ],
            'systems' => [
                'type' => ProjectSystemCreateInput::list(),
            ],
        ];
    }
}
