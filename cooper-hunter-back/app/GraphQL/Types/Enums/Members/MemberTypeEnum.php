<?php

namespace App\GraphQL\Types\Enums\Members;

use App\Enums\Member\MemberEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class MemberTypeEnum extends GenericBaseEnumType
{
    public const NAME = 'MemberTypeEnum';
    public const ENUM_CLASS = MemberEnum::class;
}
