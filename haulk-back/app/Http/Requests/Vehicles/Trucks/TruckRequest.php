<?php

namespace App\Http\Requests\Vehicles\Trucks;

use App\Dto\Vehicles\TruckDto;
use App\Enums\Format\DateTimeEnum;
use App\Http\Requests\Vehicles\CRMVehicleRequest;
use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use App\Rules\Vehicles\Trucks\UniqueVinRule;
use Illuminate\Validation\Rule;

class TruckRequest extends CRMVehicleRequest
{
    public function rules(): array
    {
        $truck = $this->route('truck');

        return  array_merge(
            parent::rules(),
            [
                'type' => ['required', 'integer', Rule::in(array_keys(Vehicle::VEHICLE_TYPES))],
                'driver_id' => [
                    'nullable',
                    'int',
                    Rule::exists(User::TABLE_NAME, 'id'),
                    function ($attribute, $value, $fail) {
                        $user = User::find($value);
                        if (!$user || !$user->isDriver()) {
                            $fail(trans('Driver not found.'));
                        }
                    },
                    Rule::unique(Truck::TABLE_NAME, 'driver_id')
                        ->ignore(optional($truck)->id)
                ],
                'owner_id' => [
                    'required',
                    'int',
                    Rule::exists(User::TABLE_NAME, 'id'),
                    function ($attribute, $value, $fail) {
                        $user = User::find($value);
                        if (!$user || !$user->isOwner()) {
                            $fail(trans('Owner not found.'));
                        }
                    },
                ],
                'temporary_plate' => ['nullable', 'required_without:license_plate', 'string', 'alpha_dash'],
                'vin' => ['required', 'string', 'max:191', 'alpha_num', new UniqueVinRule($truck)],
                'gps_device_id' => [
                    'nullable',
                    'int',
                    Rule::exists(Device::TABLE_NAME, 'id'),
                    function ($attribute, $value, $fail) use ($truck) {
                        $device = Device::find($value);
                        if ($device->trailer || ($device->truck && ($truck && $truck->id !== $device->truck->id))) {
                            $fail(trans('This IMEI is already assigned'));
                        }
                    }
                ],
            ]
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
