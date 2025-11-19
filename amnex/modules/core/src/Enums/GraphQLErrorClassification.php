<?php

declare(strict_types=1);

namespace Wezom\Core\Enums;

enum GraphQLErrorClassification
{
    case BAD_REQUEST;
    case UNAUTHORIZED;
    case FORBIDDEN;
    case NOT_FOUND;
    case INTERNAL_ERROR;
}
