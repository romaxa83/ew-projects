<?php

namespace App\Foundations\Modules\Location\Exceptions\Timezone;

use Exception;

class IncorrectTimezoneCountryException extends Exception
{
    public function __construct()
    {
        parent::__construct(__('exceptions.localization.timezone.incorrect_country_code'));
    }
}
