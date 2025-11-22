<?php

namespace App\GraphQL\Types\Dictionaries;

use App\Models\Dictionaries\Recommendation;
use GraphQL\Type\Definition\Type;

class RecommendationType extends BaseDictionaryType
{
    public const NAME = 'RecommendationType';
    public const MODEL = Recommendation::class;

    protected string $translateTypeClass = RecommendationTranslateType::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'problems' => [
                    'type' => Type::listOf(ProblemType::nonNullType()),
                    'is_relation' => true,
                ],
                'regulations' => [
                    'type' => Type::listOf(RegulationType::nonNullType()),
                    'is_relation' => true,
                ],
            ]
        );
    }
}
