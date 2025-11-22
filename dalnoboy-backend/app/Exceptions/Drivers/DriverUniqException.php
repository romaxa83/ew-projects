<?php


namespace App\Exceptions\Drivers;


use Core\Exceptions\TranslatedException;

class DriverUniqException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.drivers.uniq'));
    }
}
