<?php

namespace App\GraphQL\Types\Projects;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Users\UserMorphType;
use App\Models\Projects\Project;

class ProjectType extends BaseType
{
    public const NAME = 'ProjectType';
    public const MODEL = Project::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'name' => [
                    'type' => NonNullType::string(),
                ],
                'member' => [
                    'type' => UserMorphType::nonNullType(),
                    'is_relation' => true,
                ],
                'systems' => [
                    'type' => ProjectSystemType::list(),
                ],
            ]
        );
    }
}
