<?php

namespace App\GraphQL\InputTypes\Dictionaries;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Rules\TranslationsArrayValidator;

abstract class BaseDictionaryInputType extends BaseInputType
{
    protected string $translateInputTypeClass;

    public function fields(): array
    {
        return [
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'translations' => [
                'type' => $this->translateInputTypeClass::nonNullList(),
                'rules' => [
                    'required',
                    'array',
                    new TranslationsArrayValidator()
                ],
            ],
        ];
    }
}
