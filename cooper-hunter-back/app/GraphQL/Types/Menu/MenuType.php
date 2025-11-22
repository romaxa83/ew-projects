<?php

namespace App\GraphQL\Types\Menu;

use App\GraphQL\Types\About\Pages\PageType;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Menu\MenuBlockTypeEnum;
use App\GraphQL\Types\Enums\Menu\MenuPositionTypeEnum;
use App\GraphQL\Types\NonNullType;
use App\Models\Menu\Menu;

class MenuType extends BaseType
{
    public const NAME = 'MenuType';
    public const MODEL = Menu::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'page' => [
                'type' => PageType::nonNullType(),
                'is_relation' => true,
            ],
            'position' => [
                'type' => MenuPositionTypeEnum::nonNullType(),
            ],
            'block' => [
                'type' => MenuBlockTypeEnum::nonNullType(),
            ],
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'translation' => [
                'type' => MenuTranslationType::nonNullType(),
            ],
            'translations' => [
                'type' => MenuTranslationType::nonNullList(),
            ],

        ];
    }
}
