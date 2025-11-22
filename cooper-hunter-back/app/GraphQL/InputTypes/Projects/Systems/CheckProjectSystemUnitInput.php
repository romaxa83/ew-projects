<?php

namespace App\GraphQL\InputTypes\Projects\Systems;

use GraphQL\Type\Definition\Type;

class CheckProjectSystemUnitInput extends ProjectSystemCreateInput
{
    public const NAME = 'CheckProjectSystemUnitInput';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
                'description' => 'Если передать ID, то будет отредактирована выбранная система. иначе будет добавлена новая система',
            ],
            'units' => [
                'type' => ProjectSystemUnitInput::list(),
                'rules' => ['sometimes', 'nullable', 'array'],
            ],
        ];
    }
}
