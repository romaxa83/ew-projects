<?php

namespace App\GraphQL\InputTypes\Warranty\WarrantyInfo;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\FileType;
use App\GraphQL\Types\NonNullType;
use App\Rules\TranslationsArrayValidator;

class WarrantyInfoInput extends BaseInputType
{
    public const NAME = 'WarrantyInfoInput';

    public function fields(): array
    {
        return [
            'video_link' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'url'],
            ],
            'pdf' => [
                'type' => FileType::type(),
                'rules' => ['nullable', 'mimes:pdf'],
            ],
            'translations' => [
                'type' => WarrantyInfoTranslationInput::nonNullList(),
                'rules' => [new TranslationsArrayValidator()],
            ],
            'packages' => [
                'type' => WarrantyInfoPackageInput::nonNullList(),
            ],
        ];
    }
}
