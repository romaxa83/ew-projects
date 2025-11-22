<?php

namespace App\GraphQL\Types\Commercial\Commissioning;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Commercial\Commissioning\AnswerPhotoTypeEnumType;
use App\GraphQL\Types\Enums\Commercial\Commissioning\AnswerTypeEnumType;
use App\GraphQL\Types\Enums\Commercial\Commissioning\QuestionStatusEnumType;
use App\Models\Commercial\Commissioning\Question;
use Core\Traits\Auth\AuthGuardsTrait;
use GraphQL\Type\Definition\Type;

class QuestionType extends BaseType
{
    use AuthGuardsTrait;

    public const NAME = 'CommissioningQuestionType';
    public const MODEL = Question::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'answer_type' => [
                    'type' => AnswerTypeEnumType::nonNullType(),
                ],
                'photo_type' => [
                    'type' => AnswerPhotoTypeEnumType::nonNullType(),
                ],
                'status' => [
                    'type' => QuestionStatusEnumType::nonNullType(),
                ],
                'sort' => [
                    'type' => Type::int(),
                ],
                'translation' => [
                    'type' => QuestionTranslationType::nonNullType(),
                ],
                'translations' => [
                    'type' => QuestionTranslationType::nonNullList(),
                ],
                'option_answers' => [
                    'type' => OptionAnswerType::list(),
                    'is_relation' => true,
                    'alias' => 'optionAnswers',
                ],
            ],
        );
    }
}

