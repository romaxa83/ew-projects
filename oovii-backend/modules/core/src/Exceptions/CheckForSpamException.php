<?php

namespace WezomCms\Core\Exceptions;

use Throwable;

class CheckForSpamException extends \Exception
{
    /**
     * CheckForSpamException constructor.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  Throwable|null  $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (!$message) {
            $message = app('isBackend')
                ? __('cms-core::admin.auth.For security reasons your request has been canceled Please try again later')
                : __('cms-core::site.For security reasons your request has been canceled Please try again later');
        }

        parent::__construct($message, $code, $previous);
    }
}
