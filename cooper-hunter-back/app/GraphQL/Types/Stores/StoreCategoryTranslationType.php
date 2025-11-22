<?php

namespace App\GraphQL\Types\Stores;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Stores\StoreCategoryTranslation;

class StoreCategoryTranslationType extends BaseTranslationType
{
    public const NAME = 'StoreCategoryTranslationType';
    public const MODEL = StoreCategoryTranslation::class;

    public function fields(): array
    {
        return parent::fields() + [
                'title' => [
                    'type' => NonNullType::string(),
                ],
            ];
    }
}
