<?php

namespace App\GraphQL\InputTypes\Stores\Stores;

use App\GraphQL\InputTypes\BaseTranslationInput;
use App\GraphQL\Types\NonNullType;

class StoreTranslationInput extends BaseTranslationInput
{
    public const NAME = 'StoreTranslationInput';

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
