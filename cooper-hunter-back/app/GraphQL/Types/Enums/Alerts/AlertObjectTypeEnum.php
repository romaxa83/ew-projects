<?php

namespace App\GraphQL\Types\Enums\Alerts;

use App\Enums\Alerts\AlertModelEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class AlertObjectTypeEnum extends GenericBaseEnumType
{

    public const NAME = 'AlertObjectTypeEnum';
    public const DESCRIPTION = 'Available list of object which can send in notification.';
    public const ENUM_CLASS = AlertModelEnum::class;
}
