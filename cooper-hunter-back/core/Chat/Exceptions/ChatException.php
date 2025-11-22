<?php

namespace Core\Chat\Exceptions;

use GraphQL\Error\ClientAware;
use GraphQL\Error\Error;
use RuntimeException;

class ChatException extends RuntimeException implements ClientAware
{
    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return Error::CATEGORY_INTERNAL;
    }
}
