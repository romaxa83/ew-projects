<?php

namespace App\GraphQL\Types\Catalog\Categories;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class CategoryBreadcrumbType extends BaseType
{
    public const NAME = 'CategoryBreadcrumbType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'parent_id' => [
                'type' => Type::id(),
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'slug' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
