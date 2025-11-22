<?php

namespace App\GraphQL\Types\Stores;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Stores\StoreTranslation;

class StoreTranslationType extends BaseTranslationType
{
    public const NAME = 'StoreTranslationType';
    public const MODEL = StoreTranslation::class;

    public function fields(): array
    {
        return parent::fields() + [
                'title' => [
                    'type' => NonNullType::string(),
                ],
            ];
    }
}
