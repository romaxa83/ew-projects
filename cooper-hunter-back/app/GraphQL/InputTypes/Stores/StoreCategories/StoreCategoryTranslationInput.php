<?php

namespace App\GraphQL\InputTypes\Stores\StoreCategories;

use App\GraphQL\InputTypes\BaseTranslationInput;
use App\GraphQL\Types\NonNullType;

class StoreCategoryTranslationInput extends BaseTranslationInput
{
    public const NAME = 'StoreCategoryTranslationInput';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'title' => [
                    'type' => NonNullType::string(),
                ],
            ]
        );
    }
}
