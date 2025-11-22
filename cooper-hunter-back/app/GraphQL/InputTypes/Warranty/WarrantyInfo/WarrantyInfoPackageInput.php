<?php

namespace App\GraphQL\InputTypes\Warranty\WarrantyInfo;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\FileType;
use App\Rules\TranslationsArrayValidator;

class WarrantyInfoPackageInput extends BaseInputType
{
    public const NAME = 'WarrantyInfoPackageInput';

    public function fields(): array
    {
        return [
            'image' => [
                'type' => FileType::type(),
                'rules' => ['nullable', 'image'],
            ],
            'translations' => [
                'type' => WarrantyInfoPackageTranslationInput::nonNullList(),
                'rules' => [new TranslationsArrayValidator()],
            ],
        ];
    }
}
