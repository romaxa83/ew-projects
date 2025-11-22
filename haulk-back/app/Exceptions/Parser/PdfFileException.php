<?php

namespace App\Exceptions\Parser;

use Exception;
use Throwable;

class PdfFileException extends Exception implements Throwable
{
    public function __construct(?string $message = null)
    {
        parent::__construct(trans('validation.custom.parser.' . ($message ?? 'file_error')));
    }
}
