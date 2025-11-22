<?php

namespace Core\Chat\Exceptions;

use Throwable;

class MessageableException extends ChatException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        $message = $message ?: 'User must implement "\Core\Chat\Contracts\Messageable" interface';

        parent::__construct($message, $code, $previous);
    }
}
