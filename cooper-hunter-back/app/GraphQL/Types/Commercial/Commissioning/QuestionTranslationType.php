<?php

namespace App\GraphQL\Types\Commercial\Commissioning;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\QuestionTranslation;
use GraphQL\Type\Definition\Type;

class QuestionTranslationType extends BaseType
{
    public const NAME = 'CommissioningQuestionTranslationType';
    public const MODEL = QuestionTranslation::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'text' => [
                'type' => Type::string(),
            ],
            'language' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}


