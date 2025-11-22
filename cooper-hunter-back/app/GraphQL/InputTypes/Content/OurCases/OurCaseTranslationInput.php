<?php

namespace App\GraphQL\InputTypes\Content\OurCases;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Localization\Language;
use Illuminate\Validation\Rule;

class OurCaseTranslationInput extends BaseInputType
{
    public const NAME = 'OurCaseTranslationInput';

    public function fields(): array
    {
        return [
            'language' => [
                'description' => 'Язык (en, es)',
                'type' => NonNullType::string(),
                'rules' => ['max:3', Rule::exists(Language::TABLE, 'slug')]
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'description' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
