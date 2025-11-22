<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'vehicles.*.id' => ['nullable', 'integer', 'exists:App\Models\Orders\Vehicle,id'],
            'vehicles.*.inop' => ['boolean'],
            'vehicles.*.enclosed' => ['boolean'],
            'vehicles.*.vin' => ['nullable', 'string', 'max:255',],
            'vehicles.*.year' => ['nullable', 'string', 'max:4'],
            'vehicles.*.make' => ['required', 'string', 'max:255'],
            'vehicles.*.model' => ['required', 'string', 'max:255'],
            'vehicles.*.type_id' => ['required', 'integer', Rule::in(array_keys(Vehicle::VEHICLE_TYPES))],
            'vehicles.*.color' => ['nullable', 'string', 'max:255'],
            'vehicles.*.license_plate' => ['nullable', 'string', 'max:255'],
            'vehicles.*.odometer' => ['nullable', 'numeric'],
            'vehicles.*.stock_number' => ['nullable', 'string', 'max:255'],
        ];
    }
}
