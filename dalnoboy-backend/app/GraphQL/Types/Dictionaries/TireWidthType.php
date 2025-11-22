<?php

namespace App\GraphQL\Types\Dictionaries;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireWidth;

class TireWidthType extends BaseType
{
    public const NAME = 'TireWidthType';
    public const MODEL = TireWidth::class;

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
