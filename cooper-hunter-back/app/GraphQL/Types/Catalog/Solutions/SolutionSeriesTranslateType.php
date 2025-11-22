<?php

namespace App\GraphQL\Types\Catalog\Solutions;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Solutions\Series\SolutionSeriesTranslation;
use GraphQL\Type\Definition\Type;

class SolutionSeriesTranslateType extends BaseType
{
    public const NAME = 'SolutionSeriesTranslateType';
    public const MODEL = SolutionSeriesTranslation::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'description' => [
                'type' => Type::string(),
            ],
            'language' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
