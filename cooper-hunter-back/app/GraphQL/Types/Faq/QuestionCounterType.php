<?php

namespace App\GraphQL\Types\Faq;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class QuestionCounterType extends BaseType
{
    public const NAME = 'QuestionCounterType';

    public function fields(): array
    {
        return [
            'without_answer' => [
                'type' => NonNullType::int(),
            ],
            'with_answer' => [
                'type' => NonNullType::int(),
            ],
            'total' => [
                'type' => NonNullType::int(),
            ],
        ];
    }
}
