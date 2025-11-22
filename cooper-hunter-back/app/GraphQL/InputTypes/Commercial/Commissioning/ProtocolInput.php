<?php

namespace App\GraphQL\InputTypes\Commercial\Commissioning;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Commercial\Commissioning\ProtocolTranslationInputType;
use App\GraphQL\Types\Enums\Commercial\Commissioning\ProtocolTypeEnumType;
use App\Rules\TranslationsArrayValidator;

class ProtocolInput extends BaseInputType
{
    public const NAME = 'CommissioningProtocolInput';

    public function fields(): array
    {
        return [
            'type' => [
                'type' => ProtocolTypeEnumType::Type(),
                'description' => 'Protocol type',
            ],
            'translations' => [
                'type' => ProtocolTranslationInputType::nonNullList(),
                'rules' => ['required', 'array', new TranslationsArrayValidator()]
            ],
        ];
    }
}

