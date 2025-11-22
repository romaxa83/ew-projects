<?php

namespace App\Http\Requests\Vehicles\Trailers;

use App\Dto\Vehicles\TrailerDto;
use App\Enums\Format\DateTimeEnum;
use App\Http\Requests\Vehicles\CRMVehicleRequest;
use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Vehicle;
use App\Rules\Vehicles\Trailers\UniqueVinRule;
use Illuminate\Validation\Rule;

class TrailerRequest extends CRMVehicleRequest
{
    public function rules(): array
    {
        $trailer = $this->route('trailer');

        return array_merge(
            parent::rules(),
            [
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
                    Rule::unique(Trailer::TABLE_NAME, 'driver_id')
                        ->ignore(optional($trailer)->id)
                ],
                'driver_attach_at' => ['nullable', 'string', 'date_format:' . DateTimeEnum::DATE_TIME_FRONT],
//                'driver_attach_at' => ['required_with:driver_id', 'string', 'date_format:' . DateTimeEnum::DATE_TIME_FRONT],
                'owner_id' => [
                    'required',
                    'int',
                    Rule::exists(User::TABLE_NAME, 'id'),
                    function ($attribute, $value, $fail) {
                        $user = User::find($value);
                        if (!$user || !in_array($user->getRoleName(), [User::OWNER_ROLE, User::OWNER_DRIVER_ROLE])) {
                            $fail(trans('Owner not found.'));
                        }
                    },
                ],
                'temporary_plate' => ['nullable', 'required_without:license_plate', 'string', 'alpha_dash'],
                'vin' => ['required', 'string', 'max:191', 'alpha_num', new UniqueVinRule($trailer)],
                'gps_device_id' => [
                    'nullable',
                    'int',
                    Rule::exists(Device::TABLE_NAME, 'id'),
                    function ($attribute, $value, $fail) use ($trailer) {
                        $device = Device::find($value);
                        if ($device->truck || ($device->trailer && ($trailer && $trailer->id !== $device->trailer->id))) {
                            $fail(trans('This IMEI is already assigned'));
                        }
                    }
                ],
            ]
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
