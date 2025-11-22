<?php

namespace App\Http\Exceptions;

use App\Contracts\Exceptions\Http\ApiAware;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class HttpTranslatedException extends RuntimeException implements ApiAware
{
    public function __construct(
        string $message = "",
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getHttpCode(): int
    {
        return $this->code;
    }

    public function getCategory(): string
    {
        return 'translated';
    }
}
