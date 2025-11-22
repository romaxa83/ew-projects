<?php

namespace App\GraphQL\Types\Catalog\Categories;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Categories\Category;
use App\Traits\GraphQL\HasGuidTrait;

class SimpleCategoryType extends BaseType
{
    use HasGuidTrait;

    public const NAME = 'SimpleCategoryType';
    public const MODEL = Category::class;

    public function fields(): array
    {
        $fields = [
            'id' => [
                'type' => NonNullType::id()
            ],
            'slug' => [
                'type' => NonNullType::string(),
            ],
            'translation' => [
                'type' => CategoryTranslateType::nonNullType(),
                'is_relation' => true,
            ],
        ];

        return array_merge(
            $this->getGuidField(),
            $fields
        );
    }
}
