<?php

namespace App\GraphQL\InputTypes\Dictionaries;

use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class RecommendationInputType extends BaseDictionaryInputType
{
    public const NAME = 'RecommendationInputType';

    protected string $translateInputTypeClass = RecommendationTranslateInputType::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'problems' => [
                    'type' => Type::listOf(NonNullType::id()),
                ],
                'regulations' => [
                    'type' => Type::listOf(NonNullType::id()),
                ],
            ]
        );
    }
}
