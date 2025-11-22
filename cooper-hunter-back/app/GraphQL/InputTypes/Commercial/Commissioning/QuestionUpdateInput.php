<?php

namespace App\GraphQL\InputTypes\Commercial\Commissioning;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Commercial\Commissioning\QuestionTranslationInputType;
use App\GraphQL\Types\Enums\Commercial\Commissioning\AnswerPhotoTypeEnumType;
use App\GraphQL\Types\Enums\Commercial\Commissioning\AnswerTypeEnumType;
use App\GraphQL\Types\Enums\Commercial\Commissioning\QuestionStatusEnumType;
use App\Rules\TranslationsArrayValidator;
use GraphQL\Type\Definition\Type;

class QuestionUpdateInput extends BaseInputType
{
    public const NAME = 'CommissioningQuestionUpdateInput';

    public function fields(): array
    {
        return [
            'answer_type' => [
                'type' => AnswerTypeEnumType::Type(),
                'description' => 'Answer type',
            ],
            'photo_type' => [
                'type' => AnswerPhotoTypeEnumType::Type(),
                'description' => 'Have a photo in the answer',
            ],
            'status' => [
                'type' => QuestionStatusEnumType::Type(),
                'description' => 'Question status',
            ],
            'translations' => [
                'type' => QuestionTranslationInputType::nonNullList(),
                'rules' => [
                    'required',
                    'array',
                    new TranslationsArrayValidator()
                ]
            ],
        ];
    }
}


