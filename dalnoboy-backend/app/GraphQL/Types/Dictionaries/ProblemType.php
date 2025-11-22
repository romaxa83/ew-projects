<?php

namespace App\GraphQL\Types\Dictionaries;

use App\Models\Dictionaries\Problem;
use GraphQL\Type\Definition\Type;

class ProblemType extends BaseDictionaryType
{
    public const NAME = 'ProblemType';
    public const MODEL = Problem::class;

    protected string $translateTypeClass = ProblemTranslateType::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'recommendations' => [
                    'type' => Type::listOf(RecommendationType::nonNullType()),
                    'is_relation' => true,
                ],
            ]
        );
    }
}
