<?php

namespace App\GraphQL\InputTypes\Commercial\Commissioning;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Commercial\Commissioning\QuestionTranslationInputType;
use App\GraphQL\Types\Enums\Commercial\Commissioning\AnswerPhotoTypeEnumType;
use App\GraphQL\Types\Enums\Commercial\Commissioning\AnswerTypeEnumType;
use App\GraphQL\Types\Enums\Commercial\Commissioning\QuestionStatusEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\Protocol;
use App\Rules\TranslationsArrayValidator;
use Illuminate\Validation\Rule;

class QuestionInput extends BaseInputType
{
    public const NAME = 'CommissioningQuestionInput';

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
            'protocol_id' => [
                'type' => NonNullType::id(),
                ['required', 'int', Rule::exists(Protocol::class, 'id')]
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


