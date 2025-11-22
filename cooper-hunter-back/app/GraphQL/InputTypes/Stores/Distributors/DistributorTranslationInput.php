<?php

namespace App\GraphQL\InputTypes\Stores\Distributors;

use App\GraphQL\InputTypes\BaseTranslationInput;
use App\GraphQL\Types\NonNullType;

class DistributorTranslationInput extends BaseTranslationInput
{
    public const NAME = 'DistributorTranslation';

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
