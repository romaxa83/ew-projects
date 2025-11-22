<?php

namespace App\GraphQL\Types\Stores;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Stores\Store;

class StoreType extends BaseType
{
    public const NAME = 'StoreType';
    public const MODEL = Store::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'link' => [
                'type' => NonNullType::string(),
            ],
            'translation' => [
                'type' => StoreTranslationType::nonNullType(),
            ],
            'translations' => [
                'type' => StoreTranslationType::nonNullList(),
            ],
            'category' => [
                'type' => StoreCategoryType::nonNullType(),
            ],
        ];
    }
}
