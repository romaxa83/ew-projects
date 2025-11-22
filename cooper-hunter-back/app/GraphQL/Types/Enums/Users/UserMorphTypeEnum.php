<?php

namespace App\GraphQL\Types\Enums\Users;

use App\Enums\Users\UserMorphEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class UserMorphTypeEnum extends GenericBaseEnumType
{
    public const NAME = 'UserMorphTypeEnum';
    public const DESCRIPTION = 'List available users types';
    public const ENUM_CLASS = UserMorphEnum::class;
}
