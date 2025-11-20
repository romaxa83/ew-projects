<?php

declare(strict_types=1);

namespace Core\Dto;

use Core\GraphQL\Types\Inputs\GqlInputDto;
use Core\Traits\GraphQL\Types\GqlInputGenericTrait;

abstract class BaseGqlInputDto implements GqlInputDto
{
    use GqlInputGenericTrait;
}
