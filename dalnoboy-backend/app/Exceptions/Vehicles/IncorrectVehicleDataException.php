<?php


namespace App\Exceptions\Vehicles;


use Core\Exceptions\TranslatedException;

class IncorrectVehicleDataException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.vehicles.incorrect_vehicle_data'));
    }
}
