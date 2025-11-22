<?php

namespace App\GraphQL\InputTypes\Dictionaries;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class TireDiameterInputType extends BaseInputType
{
    public const NAME = 'TireDiameterInputType';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'value' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
