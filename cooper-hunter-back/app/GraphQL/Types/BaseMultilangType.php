<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;

abstract class BaseMultilangType extends BaseType
{
    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'translation' => [
                    'type' => Type::nonNull($this->getTranslationType()),
                    'is_relation' => true,
                ],
                'translations' => [
                    'type' => Type::nonNull(Type::listOf($this->getTranslationType())),
                    'is_relation' => true,
                ]
            ]
        );
    }

    abstract protected function getTranslationType(): Type;
}
