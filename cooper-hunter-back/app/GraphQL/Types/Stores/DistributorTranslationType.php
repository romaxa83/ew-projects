<?php

namespace App\GraphQL\Types\Stores;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Stores\DistributorTranslation;

class DistributorTranslationType extends BaseTranslationType
{
    public const NAME = 'DistributorTranslationType';
    public const MODEL = DistributorTranslation::class;

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
