<?php

declare(strict_types=1);

namespace Core\Enums;

use Core\GraphQL\Types\Enums\GqlTypeEnum;
use Core\Traits\GraphQL\Types\GqlEnumGenericTrait;

abstract class BaseGqlEnum extends BaseEnum implements GqlTypeEnum
{
    use GqlEnumGenericTrait;
}
