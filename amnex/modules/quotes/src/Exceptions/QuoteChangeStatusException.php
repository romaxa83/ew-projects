<?php

namespace Wezom\Quotes\Exceptions;

use Exception;
use Illuminate\Http\Response;

class QuoteChangeStatusException extends Exception
{
    public function __construct(
        ?string $msg = null,
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR
    ) {
        parent::__construct($msg ?? __('Error changing status'), $code);
    }
}
