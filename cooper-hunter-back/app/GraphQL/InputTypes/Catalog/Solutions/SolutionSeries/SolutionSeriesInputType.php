<?php

namespace App\GraphQL\InputTypes\Catalog\Solutions\SolutionSeries;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Rules\TranslationsArrayValidator;

class SolutionSeriesInputType extends BaseInputType
{
    public const NAME = 'SolutionSeriesInputType';

    public function fields(): array
    {
        return [
            'slug' => [
                'type' => NonNullType::string(),
            ],
            'translations' => [
                'type' => SolutionSeriesTranslationInputType::nonNullList(),
                'rules' => ['required', 'array', new TranslationsArrayValidator()],
            ],
        ];
    }
}
