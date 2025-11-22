<?php

namespace App\Http\Requests\BodyShop\Vehicles\Trailers;

use App\Dto\BodyShop\Vehicles\TrailerDto;
use App\Http\Requests\Vehicles\VehicleRequest;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Vehicles\Vehicle;
use Illuminate\Validation\Rule;

class TrailerRequest extends VehicleRequest
{
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'license_plate' => ['required', 'string', 'alpha_dash'],
                'owner_id' => ['required', 'int', Rule::exists(VehicleOwner::TABLE_NAME, 'id')],
            ],
        );
    }

    public function getDto(): TrailerDto
    {
        return TrailerDto::byParams($this->validated());
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->route('trailer');
    }
}
