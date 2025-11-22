<?php

namespace App\GraphQL\Types\Commercial\Commissioning;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Commercial\Commissioning\AnswerStatusEnumType;
use App\Models\Commercial\Commissioning\ProjectProtocolQuestion;
use GraphQL\Type\Definition\Type;

class ProjectProtocolQuestionType extends BaseType
{
    public const NAME = 'ProjectProtocolQuestionType';
    public const MODEL = ProjectProtocolQuestion::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
            ],
            'answer_status' => [
                'type' => AnswerStatusEnumType::nonNullType(),
            ],
            'question' => [
                'type' => QuestionType::nonNullType(),
            ],
            'answer' => [
                'type' => AnswerType::Type(),
            ],
        ];
    }
}




