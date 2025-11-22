<?php

namespace App\GraphQL\Types\Dictionaries;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireDiameter;

class TireDiameterType extends BaseType
{
    public const NAME = 'TireDiameterType';
    public const MODEL = TireDiameter::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'active' => [
                    'type' => NonNullType::boolean(),
                ],
                'value' => [
                    'type' => NonNullType::string(),
                ],
            ]
        );
    }
}
