<?php


namespace App\GraphQL\InputTypes\Localization;


use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Localization\LanguageEnumType;
use App\GraphQL\Types\NonNullType;

class TranslateInputType extends BaseInputType
{
    public const NAME = 'TranslateInputType';

    public function fields(): array
    {
        return [
            'place' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                    'min:3'
                ]
            ],
            'key' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                    'min:3'
                ]
            ],
            'text' => [
                'type' => NonNullType::string(),
            ],
            'lang' => [
                'type' => LanguageEnumType::nonNullType()
            ]
        ];
    }
}
