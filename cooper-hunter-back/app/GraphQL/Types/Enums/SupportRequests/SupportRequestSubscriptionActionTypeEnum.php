<?php

namespace App\GraphQL\Types\Enums\SupportRequests;

use App\Enums\SupportRequests\SupportRequestSubscriptionActionEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class SupportRequestSubscriptionActionTypeEnum extends GenericBaseEnumType
{
    public const NAME = 'SupportRequestSubscriptionActionTypeEnum';
    public const DESCRIPTION = '';
    public const ENUM_CLASS = SupportRequestSubscriptionActionEnum::class;
}
