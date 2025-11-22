<?php

namespace App\Exceptions\Timezone;

use Exception;

class IncorrectTimezoneCountryException extends Exception
{
    public function __construct()
    {
        parent::__construct(trans('Incorrect country code.'));
    }
}
