<?php


namespace App\Exceptions\Dictionaries;


use App\Models\Dictionaries\VehicleModel;
use Core\Exceptions\TranslatedException;

class NotUniqVehicleModelException extends TranslatedException
{
    public function __construct(private VehicleModel $vehicleModel)
    {
        parent::__construct(trans('validation.custom.dictionaries.not_uniq_model'));
    }

    public function getSimilarVehicleModel(): VehicleModel
    {
        return $this->vehicleModel;
    }
}
