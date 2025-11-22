<?php

namespace App\Exceptions\Catalog;

use Core\Exceptions\TranslatedException;

class SerialNumberNotFoundException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.catalog.serial_numbers.not_found'));
    }
}
