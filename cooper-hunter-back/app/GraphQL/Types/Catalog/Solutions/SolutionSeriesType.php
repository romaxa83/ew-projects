<?php

namespace App\GraphQL\Types\Catalog\Solutions;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Solutions\Series\SolutionSeries;

class SolutionSeriesType extends BaseType
{
    public const NAME = 'SolutionSeriesType';
    public const MODEL = SolutionSeries::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'slug' => [
                'type' => NonNullType::string(),
            ],
            'translation' => [
                'type' => SolutionSeriesTranslateType::nonNullType(),
            ],
            'translations' => [
                'type' => SolutionSeriesTranslateType::nonNullList(),
            ],
        ];
    }
}
