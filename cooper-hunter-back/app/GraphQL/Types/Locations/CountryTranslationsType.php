<?php

namespace App\GraphQL\Types\Locations;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Locations\CountryTranslation;

class CountryTranslationsType extends BaseType
{
    public const NAME = 'CountryTranslationsType';
    public const MODEL = CountryTranslation::class;

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
