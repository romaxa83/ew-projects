<?php

namespace App\GraphQL\Types\Catalog\Labels;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Catalog\Labels\ColorTypeEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Labels\Label;

class LabelType extends BaseType
{
    public const NAME = 'LabelType';
    public const MODEL = Label::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'sort' => [
                'type' => NonNullType::int(),
            ],
            'color_type' => [
                'type' => ColorTypeEnumType::nonNullType(),
            ],
            'text_color' => [
                'type' => NonNullType::string(),
                'description' => 'цвет текста',
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(Label $model) =>  $model->color_type->getTextColor()
            ],
            'background_color' => [
                'type' => NonNullType::string(),
                'description' => 'цвет фона',
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(Label $model) =>  $model->color_type->getBackgroundColor()
            ],
            'translation' => [
                'type' => LabelTranslationType::nonNullType(),
            ],
            'translations' => [
                'type' => LabelTranslationType::nonNullList(),
            ],
        ];
    }
}
