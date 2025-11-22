<?php

namespace App\Documents\Filters\Exceptions;

use Exception;
use Throwable;

class DocumentFilterMethodNotFoundException extends Exception implements Throwable
{
    public function __construct(string $method, string $filterName)
    {
        parent::__construct(sprintf("Filter method [%s] not found in [%s]", $method, $filterName));
    }
}
