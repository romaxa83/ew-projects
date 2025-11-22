<?php


namespace App\GraphQL\Types\Locations;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Localization\LanguageEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Locations\RegionTranslate;

class RegionTranslateType extends BaseType
{
    public const NAME = 'RegionTranslateType';
    public const MODEL = RegionTranslate::class;

    public function fields(): array
    {
        return [
            'title' => [
                'type' => NonNullType::string(),
            ],
            'language' => [
                'type' => LanguageEnumType::nonNullType(),
            ],
        ];
    }
}
