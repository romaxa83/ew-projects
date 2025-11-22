<?php

namespace App\GraphQL\InputTypes\Commercial\Commissioning;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Commercial\Commissioning\OptionAnswerTranslationInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\Question;
use App\Rules\TranslationsArrayValidator;
use Illuminate\Validation\Rule;

class OptionAnswerInput extends BaseInputType
{
    public const NAME = 'CommissioningOptionAnswerInput';

    public function fields(): array
    {
        return [
            'question_id' => [
                'type' => NonNullType::id(),
                ['required', 'int', Rule::exists(Question::class, 'id')]
            ],
            'translations' => [
                'type' => OptionAnswerTranslationInputType::nonNullList(),
                'rules' => [
                    'required',
                    'array',
                    new TranslationsArrayValidator()
                ]
            ],
        ];
    }
}



