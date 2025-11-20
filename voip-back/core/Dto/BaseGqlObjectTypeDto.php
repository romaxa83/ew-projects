<?php

declare(strict_types=1);

namespace Core\Dto;

use Core\GraphQL\Types\Types\GqlTypeDto;
use Core\Traits\GraphQL\Types\GqlTypeGenericTrait;

abstract class BaseGqlObjectTypeDto implements GqlTypeDto
{
    use GqlTypeGenericTrait;
}
