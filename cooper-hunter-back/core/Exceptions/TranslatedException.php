<?php

declare(strict_types=1);

namespace Core\Exceptions;

use GraphQL\Error\ClientAware;
use RuntimeException;

class TranslatedException extends RuntimeException implements ClientAware
{
    public const CATEGORY_TRANSLATED = 'translated';

    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return self::CATEGORY_TRANSLATED;
    }
}
