<?php

namespace App\GraphQL\Types\Projects;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Projects\Systems\WarrantyStatusEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Projects\System;
use GraphQL\Type\Definition\Type;

class ProjectSystemType extends BaseType
{
    public const NAME = 'ProjectSystemType';
    public const MODEL = System::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'name' => [
                    'type' => NonNullType::string(),
                ],
                'description' => [
                    'type' => Type::string(),
                ],
                'warranty_status' => [
                    'type' => WarrantyStatusEnumType::nonNullType(),
                ],
                'project_id' => [
                    'type' => NonNullType::id(),
                ],
                'units' => [
                    'type' => ProjectSystemUnitType::list(),
                ],
            ]
        );
    }
}
