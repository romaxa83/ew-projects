<?php


namespace App\Exceptions\Dictionaries;


use App\Models\Dictionaries\VehicleMake;
use Core\Exceptions\TranslatedException;

class NotUniqVehicleMakeException extends TranslatedException
{
    public function __construct(private VehicleMake $vehicleMake)
    {
        parent::__construct(trans('validation.custom.dictionaries.not_uniq_make'));
    }

    public function getSimilarVehicleMake(): VehicleMake
    {
        return $this->vehicleMake;
    }
}
