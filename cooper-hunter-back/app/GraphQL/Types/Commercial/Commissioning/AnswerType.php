<?php

namespace App\GraphQL\Types\Commercial\Commissioning;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\Answer;
use GraphQL\Type\Definition\Type;

class AnswerType extends BaseType
{
    public const NAME = 'CommissioningAnswerType';
    public const MODEL = Answer::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'text' => [
                'type' => Type::string(),
            ],
            'option_answers' => [
                'type' => OptionAnswerType::list(),
                'is_relation' => true,
                'alias' => 'optionAnswers',
            ],
            'images' => [
                'type' => MediaType::list(),
                'always' => 'id',
                'alias' => 'media',
            ],
        ];
    }
}


