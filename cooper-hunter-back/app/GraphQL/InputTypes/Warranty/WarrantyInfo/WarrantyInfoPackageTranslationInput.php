<?php

namespace App\GraphQL\InputTypes\Warranty\WarrantyInfo;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Localization\Language;
use Illuminate\Validation\Rule;

class WarrantyInfoPackageTranslationInput extends BaseInputType
{
    public const NAME = 'WarrantyInfoPackageTranslationInput';

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
