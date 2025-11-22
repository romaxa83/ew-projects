<?php


namespace App\Exceptions\Vehicles\Schemas;


use Core\Exceptions\TranslatedException;

class WheelNotFoundException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.vehicles.schemas.wheels_not_found'));
    }
}
