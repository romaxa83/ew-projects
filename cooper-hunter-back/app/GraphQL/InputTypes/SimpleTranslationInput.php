<?php

namespace App\GraphQL\InputTypes;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Localization\Language;
use Illuminate\Validation\Rule;

class SimpleTranslationInput extends BaseInputType
{
    public const NAME = 'SimpleTranslationInput';

    public function fields(): array
    {
        return [
            'language' => [
                'description' => 'Язык (en, es)',
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                    'max:3',
                    Rule::exists(Language::class, 'slug')
                ]
            ],
            'title' => [
                'description' => 'Название',
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                    'max:250'
                ]
            ],
        ];
    }
}
