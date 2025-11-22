<?php

namespace App\GraphQL\InputTypes\News\Videos;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Rules\TranslationsArrayValidator;
use GraphQL\Type\Definition\Type;

class VideoCreateInput extends BaseInputType
{
    public const NAME = 'VideoCreateInput';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => Type::boolean(),
            ],
            'slug' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                ],
            ],
            'translations' => [
                'type' => VideoTranslationInput::nonNullList(),
                'rules' => [new TranslationsArrayValidator()],
            ],
            'created_at' => [
                'type' => Type::int(),
                'description' => 'Timestamps in second'
            ],
        ];
    }
}
