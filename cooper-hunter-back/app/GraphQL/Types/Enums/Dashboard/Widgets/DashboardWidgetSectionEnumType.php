<?php

namespace App\GraphQL\Types\Enums\Dashboard\Widgets;

use App\Enums\Dashboard\Widgets\DashboardWidgetSectionEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class DashboardWidgetSectionEnumType extends GenericBaseEnumType
{
    public const NAME = 'DashboardWidgetSectionEnumType';
    public const ENUM_CLASS = DashboardWidgetSectionEnum::class;
}