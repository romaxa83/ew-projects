<?php

namespace App\GraphQL\Types\Enums\Dashboard\Widgets;

use App\Enums\Dashboard\Widgets\DashboardWidgetTypeEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class DashboardWidgetTypeEnumType extends GenericBaseEnumType
{
    public const NAME = 'DashboardWidgetTypeEnumType';
    public const ENUM_CLASS = DashboardWidgetTypeEnum::class;
}