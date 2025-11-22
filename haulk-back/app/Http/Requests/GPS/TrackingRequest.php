<?php

namespace App\Http\Requests\GPS;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class TrackingRequest extends FormRequest
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
            'order_type' => ['nullable', 'string', 'in:asc,desc'],
            'search' => ['nullable', 'string'],
            'device_statuses' => ['nullable', 'array'],
            'device_statuses.*' => ['string', DeviceStatus::ruleIn()],
        ];
    }
}

