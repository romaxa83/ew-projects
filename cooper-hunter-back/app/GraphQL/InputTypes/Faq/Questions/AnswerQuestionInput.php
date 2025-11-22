<?php

namespace App\GraphQL\InputTypes\Faq\Questions;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class AnswerQuestionInput extends BaseInputType
{
    public const NAME = 'AnswerQuestionInput';

    public function fields(): array
    {
        return [
            'answer' => [
                'type' => NonNullType::string(),
                'rules' => ['min:10'],
            ],
        ];
    }
}
