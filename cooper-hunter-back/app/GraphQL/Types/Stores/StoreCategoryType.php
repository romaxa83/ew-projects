<?php

namespace App\GraphQL\Types\Stores;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Stores\StoreCategory;

class StoreCategoryType extends BaseType
{
    public const NAME = 'StoreCategoryType';
    public const MODEL = StoreCategory::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'translation' => [
                'type' => StoreCategoryTranslationType::nonNullType(),
            ],
            'translations' => [
                'type' => StoreCategoryTranslationType::nonNullList(),
            ],
            'stores' => [
                'type' => StoreType::nonNullList(),
            ],
        ];
    }
}
