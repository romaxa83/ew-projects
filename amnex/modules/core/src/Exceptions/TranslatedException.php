<?php

declare(strict_types=1);

namespace Wezom\Core\Exceptions;

use GraphQL\Error\ClientAware;
use RuntimeException;

class TranslatedException extends RuntimeException implements ClientAware
{
    public function isClientSafe(): bool
    {
        return true;
    }
}
