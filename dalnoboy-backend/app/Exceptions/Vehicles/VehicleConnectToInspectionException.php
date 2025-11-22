<?php


namespace App\Exceptions\Vehicles;


use Core\Exceptions\TranslatedException;

class VehicleConnectToInspectionException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.vehicles.vehicle_connect_to_inspection'));
    }
}
