<?php

namespace App\GraphQL\InputTypes;

use GraphQL\Type\Definition\Type;

class SimpleTranslationWithDescriptionInput extends SimpleTranslationInput
{
    public const NAME = 'SimpleTranslationWithDescriptionInput';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'description' => [
                    'description' => 'Описание',
                    'type' => Type::string()
                ],
            ]
        );
    }
}
