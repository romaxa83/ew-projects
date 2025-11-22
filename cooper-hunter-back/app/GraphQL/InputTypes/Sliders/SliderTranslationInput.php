<?php

namespace App\GraphQL\InputTypes\Sliders;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Localization\Language;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class SliderTranslationInput extends BaseInputType
{
    public const NAME = 'SliderTranslationInput';

    public function fields(): array
    {
        return [
            'language' => [
                'description' => 'Язык (en, es)',
                'type' => NonNullType::string(),
                'rules' => ['max:3', Rule::exists(Language::TABLE, 'slug')]
            ],
            'title' => [
                'type' => Type::string(),
            ],
            'description' => [
                'type' => Type::string(),
            ],
        ];
    }
}
