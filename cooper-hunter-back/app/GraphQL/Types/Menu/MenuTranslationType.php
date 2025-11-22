<?php

namespace App\GraphQL\Types\Menu;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Menu\MenuTranslation;

class MenuTranslationType extends BaseTranslationType
{
    public const NAME = 'MenuTranslationType';
    public const MODEL = MenuTranslation::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'title' => [
                    'type' => NonNullType::string(),
                ]
            ]
        );
    }
}
