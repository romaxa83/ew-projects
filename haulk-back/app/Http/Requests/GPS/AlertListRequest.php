<?php

namespace App\Http\Requests\GPS;

use App\Models\GPS\Alert;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AlertListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date'],
            'truck_id' => ['nullable', 'int', Rule::exists(Truck::class, 'id')],
            'trailer_id' => ['nullable', 'int', Rule::exists(Trailer::class, 'id')],
            'device_id' => ['nullable', 'int',],
            'alert_type' => ['nullable', 'array', Rule::in(Alert::ALERT_TYPES)],
            'alert_type.*' => ['nullable', 'string', Rule::in(Alert::ALERT_TYPES)],
        ];
    }
}
