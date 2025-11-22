<?php

namespace App\Http\Requests\GPS;

use App\Models\GPS\Alert;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AlertIndexRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'int'],
            'order_type' => ['nullable', 'string', 'in:asc,desc'],
            'truck_id' => ['nullable', 'int', Rule::exists(Truck::class, 'id')],
            'trailer_id' => ['nullable', 'int', Rule::exists(Trailer::class, 'id')],
            'driver_id' => ['nullable', 'integer', Rule::exists(User::TABLE_NAME, 'id')],
            'alert_type' => ['nullable', 'string', Rule::in(Alert::ALERT_TYPES)],
            'vehicle_unit_number' => ['nullable', 'string'],
        ];
    }
}
