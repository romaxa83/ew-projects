<?php

namespace App\GraphQL\InputTypes\Catalog\Solutions\SolutionSeries;

use App\GraphQL\InputTypes\BaseTranslationInput;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class SolutionSeriesTranslationInputType extends BaseTranslationInput
{
    public const NAME = 'SolutionSeriesTranslationInputType';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'title' => [
                    'type' => NonNullType::string(),
                    'rules' => ['required', 'string'],
                ],
                'description' => [
                    'type' => Type::string(),
                    'rules' => ['nullable', 'string'],
                ],
            ]
        );
    }
}