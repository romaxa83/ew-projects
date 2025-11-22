<?php

namespace App\GraphQL\Types\Locations;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Locations\StateTranslation;

class StateTranslationsType extends BaseType
{
    public const NAME = 'StateTranslationsType';
    public const MODEL = StateTranslation::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'name' => [
                'type' => NonNullType::string(),
            ],
            'language' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
