<?php

namespace App\GraphQL\Types\Dictionaries;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireHeight;

class TireHeightType extends BaseType
{
    public const NAME = 'TireHeightType';
    public const MODEL = TireHeight::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'active' => [
                    'type' => NonNullType::boolean(),
                ],
                'value' => [
                    'type' => NonNullType::float(),
                ],
            ]
        );
    }
}
