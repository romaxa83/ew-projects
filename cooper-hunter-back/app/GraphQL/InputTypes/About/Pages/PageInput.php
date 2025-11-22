<?php

namespace App\GraphQL\InputTypes\About\Pages;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\About\Page;
use App\Rules\TranslationsArrayValidator;
use GraphQL\Type\Definition\Type;

class PageInput extends BaseInputType
{
    public const NAME = 'PageInput';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => Type::boolean(),
                'defaultValue' => Page::DEFAULT_ACTIVE
            ],
            'slug' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string']
            ],
            'translations' => [
                'type' => PageTranslationInput::nonNullList(),
                'rules' => [
                    'required',
                    'array',
                    new TranslationsArrayValidator()
                ],
            ],
        ];
    }
}
