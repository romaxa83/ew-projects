<?php

namespace App\Exceptions\Timezone;

use Exception;

class IncorrectTimezoneException extends Exception
{
    public function __construct()
    {
        parent::__construct(trans('Incorrect timezone.'));
    }
}
