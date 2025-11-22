<?php

namespace App\GraphQL\Types\Dictionaries;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

abstract class BaseDictionaryType extends BaseType
{
    protected string $translateTypeClass = '';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'active' => [
                    'type' => NonNullType::boolean(),
                ],
                'translate' => [
                    'type' => $this->translateTypeClass::nonNullType(),
                    'is_relation' => true,
                ],
                'translates' => [
                    'type' => $this->translateTypeClass::nonNullList(),
                    'is_relation' => true,
                ]
            ]
        );
    }
}
