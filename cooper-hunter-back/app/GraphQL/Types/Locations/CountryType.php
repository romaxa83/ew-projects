<?php

namespace App\GraphQL\Types\Locations;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Locations\Country;
use GraphQL\Type\Definition\Type;

class CountryType extends BaseType
{
    public const NAME = 'CountryType';
    public const MODEL = Country::class;

    public function fields(): array
    {
        return parent::fields() + [
            'name' => [
                /** @see CountryType::resolveNameField() */
                'type' => NonNullType::string(),
                'selectable' => false,
            ],
            'alias' => [
                'type' => NonNullType::string(),
            ],
            'published' => [
                'type' => NonNullType::boolean(),
                'alias' => 'active',
            ],
            'default' => [
                'type' => NonNullType::boolean(),
            ],
            'translations' => [
                'type' => NonNullType::listOf(CountryTranslationsType::nonNullType()),
            ],
            'states' => [
                'type' => StateType::list(),
            ],
            'country_code' => [
                'type' => Type::string(),
            ],
        ];
    }

    protected function resolveNameField(Country $model): string
    {
        return $model->translation->name;
    }
}

