<?php

namespace App\GraphQL\InputTypes\Menu;

use App\Enums\Menu\MenuBlockEnum;
use App\Enums\Menu\MenuPositionEnum;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Menu\MenuBlockTypeEnum;
use App\GraphQL\Types\Enums\Menu\MenuPositionTypeEnum;
use App\GraphQL\Types\NonNullType;
use App\Models\About\Page;
use App\Models\Menu\Menu;
use App\Rules\TranslationsArrayValidator;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class MenuInput extends BaseInputType
{
    public const NAME = 'MenuInput';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => Type::boolean(),
                'defaultValue' => Menu::DEFAULT_ACTIVE,
            ],
            'page_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Page::class, 'id')
                ]
            ],
            'position' => [
                'type' => MenuPositionTypeEnum::nonNullType(),
                'defaultValue' => MenuPositionEnum::FOOTER,
            ],
            'block' => [
                'type' => MenuBlockTypeEnum::nonNullType(),
                'defaultValue' => MenuBlockEnum::OTHER,
            ],
            'translations' => [
                'type' => MenuTranslationInput::nonNullList(),
                'rules' => [
                    'array',
                    'required',
                    new TranslationsArrayValidator()
                ],
            ],
        ];
    }
}
