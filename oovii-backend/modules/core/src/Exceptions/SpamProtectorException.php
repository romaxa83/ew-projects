<?php

namespace WezomCms\Core\Exceptions;

use Throwable;

class SpamProtectorException extends CheckForSpamException
{
    /**
     * @var string|null
     */
    protected $field;

    /**
     * SpamProtectorException constructor.
     *
     * @param $field
     * @param  string  $message
     * @param  int  $code
     * @param  Throwable|null  $previous
     */
    public function __construct($field, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->field = $field;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string|null
     */
    public function getField(): ?string
    {
        return $this->field;
    }
}
