<?php

namespace App\GraphQL\InputTypes\About\About;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Localization\Language;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class AboutCompanyTranslationInput extends BaseInputType
{
    public const NAME = 'AboutCompanyTranslationInput';

    public function fields(): array
    {
        return [
            'language' => [
                'description' => 'Язык (en, es)',
                'type' => NonNullType::string(),
                'rules' => ['max:3', Rule::exists(Language::TABLE, 'slug')]
            ],
            'video_link' => [
                'type' => NonNullType::string(),
                'rules' => ['url'],
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'description' => [
                'type' => NonNullType::string(),
            ],
            'short_description' => [
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
            'additional_title' => [
                'type' => Type::string(),
            ],
            'additional_description' => [
                'type' => Type::string(),
            ],
            'additional_video_link' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'url'],
            ],
        ];
    }
}
