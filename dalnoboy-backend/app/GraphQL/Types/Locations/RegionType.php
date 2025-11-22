<?php


namespace App\GraphQL\Types\Locations;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class RegionType extends BaseType
{
    public const NAME = 'RegionType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'slug' => [
                'type' => NonNullType::string(),
            ],
            'translate' => [
                'type' => RegionTranslateType::nonNullType(),
                'is_relation' => true,
            ],
            'translates' => [
                'type' => RegionTranslateType::nonNullList(),
                'is_relation' => true,
            ]
        ];
    }
}
