<?php

namespace App\GraphQL\InputTypes\Faq;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Localization\Language;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class FaqTranslationInput extends BaseInputType
{
    public const NAME = 'FaqTranslationInput';

    public function fields(): array
    {
        return [
            'language' => [
                'description' => 'Язык (en, es)',
                'type' => NonNullType::string(),
                'rules' => ['max:3', Rule::exists(Language::TABLE, 'slug')]
            ],
            'question' => [
                'type' => NonNullType::string(),
            ],
            'answer' => [
                'type' => NonNullType::string(),
            ],
            'seo_title' => [
                'type' => Type::string(),
            ],
            'seo_description' => [
                'type' => Type::string(),
            ],
            'seo_h1' => [
                'type' => Type::string(),
            ],
        ];
    }
}
