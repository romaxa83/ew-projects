<?php

namespace App\GraphQL\InputTypes\Faq\Questions;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Rules\NameRule;

class AskAQuestionInput extends BaseInputType
{
    public const NAME = 'AskAQuestionInput';

    public function fields(): array
    {
        return [
            'name' => [
                'type' => NonNullType::string(),
                'rules' => [new NameRule()],
            ],
            'email' => [
                'type' => NonNullType::string(),
                'rules' => ['email:filter'],
            ],
            'question' => [
                'type' => NonNullType::string(),
                'rules' => ['min:10'],
            ],
        ];
    }
}
