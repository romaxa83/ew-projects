<?php

namespace App\GraphQL\Types\Enums\Utils\Versioning;

use App\Enums\Utils\Versioning\VersionStatusEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class VersionStatusEnumType extends GenericBaseEnumType
{
    public const NAME = 'VersionStatusEnumType';
    public const ENUM_CLASS = VersionStatusEnum::class;
}