<?php


namespace App\Exceptions\Vehicles;


use Core\Exceptions\TranslatedException;

class NotUniqStateNumberException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.vehicles.not_uniq_state_number'));
    }
}
