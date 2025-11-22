<?php

namespace App\GraphQL\InputTypes\About\ForMemberPages;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\About\ForMemberPageEnumType;
use App\Rules\TranslationsArrayValidator;

class ForMemberPageInput extends BaseInputType
{
    public const NAME = 'ForMemberPageInput';

    public function fields(): array
    {
        return [
            'for_member_type' => [
                'type' => ForMemberPageEnumType::nonNullType(),
            ],
            'translations' => [
                'type' => ForMemberPageTranslationInput::nonNullList(),
                'rules' => [new TranslationsArrayValidator()],
            ],
        ];
    }
}
