<?php

namespace App\GraphQL\Types\Commercial\Commissioning;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\OptionAnswerTranslation;
use GraphQL\Type\Definition\Type;

class OptionAnswerTranslationType extends BaseType
{
    public const NAME = 'CommissioningOptionAnswerTranslationType';
    public const MODEL = OptionAnswerTranslation::class;

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


