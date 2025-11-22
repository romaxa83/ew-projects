<?php

namespace App\GraphQL\InputTypes\Faq;

use App\GraphQL\Types\BaseInputType;
use GraphQL\Type\Definition\Type;

class FaqCreateInput extends BaseInputType
{
    public const NAME = 'FaqCreateInput';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => Type::boolean(),
            ],
            'translations' => [
                'type' => FaqTranslationInput::nonNullList(),
            ],
        ];
    }
}
