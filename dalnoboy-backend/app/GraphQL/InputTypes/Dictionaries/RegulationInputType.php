<?php

namespace App\GraphQL\InputTypes\Dictionaries;

use GraphQL\Type\Definition\Type;

class RegulationInputType extends BaseDictionaryInputType
{
    public const NAME = 'RegulationInputType';

    protected string $translateInputTypeClass = RegulationTranslateInputType::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'days' => [
                    'type' => Type::int(),
                    'rules' => [
                        'nullable',
                        'required_without:regulation.distance',
                    ],
                ],
                'distance' => [
                    'type' => Type::int(),
                    'rules' => [
                        'nullable',
                        'required_without:regulation.days',
                    ],
                ],
            ]
        );
    }
}
