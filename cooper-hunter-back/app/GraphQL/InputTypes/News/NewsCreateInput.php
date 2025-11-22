<?php

namespace App\GraphQL\InputTypes\News;

use App\GraphQL\Types\BaseInputType;
use App\Rules\TranslationsArrayValidator;
use GraphQL\Type\Definition\Type;

class NewsCreateInput extends BaseInputType
{
    public const NAME = 'NewsCreateInput';

    public function fields(): array
    {
        return [
            'slug' => [
                'type' => Type::string(),
            ],
            'tag_id' => [
                'type' => Type::id(),
            ],
            'active' => [
                'type' => Type::boolean(),
            ],
            'translations' => [
                'type' => NewsTranslationInput::nonNullList(),
                'rules' => [new TranslationsArrayValidator()],
            ],
            'created_at' => [
                'type' => Type::int(),
                'description' => 'Timestamps in second'
            ],
        ];
    }
}
