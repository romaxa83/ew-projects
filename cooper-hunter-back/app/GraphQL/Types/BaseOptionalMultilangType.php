<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;

abstract class BaseOptionalMultilangType extends BaseType
{
    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'translation' => [
                    'type' => $this->getTranslationType(),
                    'is_relation' => true,
                ],
                'translations' => [
                    'type' => Type::listOf($this->getTranslationType()),
                    'is_relation' => true,
                ]
            ]
        );
    }

    abstract protected function getTranslationType(): Type;
}
