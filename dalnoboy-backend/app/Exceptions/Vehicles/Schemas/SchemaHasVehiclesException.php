<?php


namespace App\Exceptions\Vehicles\Schemas;


use Core\Exceptions\TranslatedException;

class SchemaHasVehiclesException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.vehicles.schemas.schema_has_vehicles'));
    }
}
