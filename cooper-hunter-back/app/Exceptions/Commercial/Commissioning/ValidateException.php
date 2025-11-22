<?php

namespace App\Exceptions\Commercial\Commissioning;

class ValidateException extends \Exception
{
    private string $field;

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null, $field = "")
    {
        parent::__construct($message, $code, $previous);

        $this->field = $field;
    }

    public function getField():string
    {
        return $this->field;
    }
}
