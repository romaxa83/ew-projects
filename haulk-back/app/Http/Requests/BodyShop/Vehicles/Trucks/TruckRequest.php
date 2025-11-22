<?php

namespace App\Http\Requests\BodyShop\Vehicles\Trucks;

use App\Dto\BodyShop\Vehicles\TruckDto;
use App\Http\Requests\Vehicles\VehicleRequest;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Vehicles\Vehicle;
use Illuminate\Validation\Rule;

class TruckRequest extends VehicleRequest
{
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'type' => ['required', 'integer', Rule::in(array_keys(Vehicle::VEHICLE_TYPES))],
                'license_plate' => ['required', 'string', 'alpha_dash'],
                'owner_id' => ['required', 'int', Rule::exists(VehicleOwner::TABLE_NAME, 'id')],
            ],
        );
    }

    public function getDto(): TruckDto
    {
        return TruckDto::byParams($this->validated());
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->route('truck');
    }
}
