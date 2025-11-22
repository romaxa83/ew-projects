<?php

namespace App\GraphQL\Types\Locations;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Locations\State;

class StateType extends BaseType
{
    public const NAME = 'StateType';

    public const MODEL = State::class;

    public function fields(): array
    {
        return parent::fields() + [
                'name' => [
                    /** @see StateType::resolveNameField() */
                    'type' => NonNullType::string(),
                    'selectable' => false,
                ],
                'short_name' => [
                    'type' => NonNullType::string(),
                ],
                'published' => [
                    'type' => NonNullType::boolean(),
                    'alias' => 'status',
                ],
                'requires_hvac_license' => [
                    'type' => NonNullType::boolean(),
                    'alias' => 'hvac_license',
                ],
                'requires_epa_license' => [
                    'type' => NonNullType::boolean(),
                    'alias' => 'epa_license',
                ],
                'translations' => [
                    'type' => NonNullType::listOf(StateTranslationsType::nonNullType()),
                ],
            ];
    }

    protected function resolveNameField(State $state): string
    {
        return $state->translation->name;
    }
}
