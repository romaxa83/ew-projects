<?php

namespace App\GraphQL\Types\Dashboard\Widgets;

use App\Dashboard\Widgets\AbstractWidget;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Dashboard\Widgets\DashboardWidgetSectionEnumType;
use App\GraphQL\Types\Enums\Dashboard\Widgets\DashboardWidgetTypeEnumType;
use App\GraphQL\Types\NonNullType;

class DashboardWidgetType extends BaseType
{
    public const NAME = 'DashboardWidgetType';

    public function fields(): array
    {
        return [
            'title' => [
                'type' => NonNullType::string(),
                'description' => 'Widget title',
                'resolve' => static fn(AbstractWidget $w): string => $w->getTitle(),
            ],
            'value' => [
                'type' => NonNullType::string(),
                'description' => 'Widget value',
                'resolve' => static fn(AbstractWidget $w): string => $w->getValue(),
            ],
            'section' => [
                'type' => DashboardWidgetSectionEnumType::nonNullType(),
                'description' => 'The section that the widget belongs to',
                'resolve' => static fn(AbstractWidget $w): string => $w->getSection()->value,
            ],
            'type' => [
                'type' => DashboardWidgetTypeEnumType::nonNullType(),
                'description' => 'Specifies what the value means',
                'resolve' => static fn(AbstractWidget $w): string => $w->getType()->value,
            ],
        ];
    }
}