<?php

namespace App\Http\Requests\Vehicles\Trucks;

use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GPSDevicesListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'truck_id' => ['nullable', 'integer', Rule::exists(Truck::TABLE_NAME, 'id')],
        ];
    }
}
