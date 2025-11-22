<?php

namespace App\GraphQL\InputTypes\Projects\Systems;

use GraphQL\Type\Definition\Type;

class ProjectSystemUpdateInput extends ProjectSystemCreateInput
{
    public const NAME = 'ProjectSystemUpdateInput';

    public function fields(): array
    {
        $parent = parent::fields();

        unset($parent['units']);

        return array_merge(
            [
                'id' => [
                    'type' => Type::id(),
                    'description' => 'Если передать ID, то будет отредактирована выбранная система. иначе будет добавлена новая система',
                ],
                'units' => [
                    'type' => ProjectSystemUnitInput::list(),
                    'rules' => ['sometimes', 'nullable', 'array'],
                ],
            ],
            $parent,
        );
    }
}
