<?php

namespace App\Http\Requests\GPS\Device;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class DeviceFilterRequest extends FormRequest
{
    use OnlyValidateForm;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', DeviceStatus::ruleIn()],
            'query' => ['nullable', 'string'],
            'page' => ['nullable', 'int'],
            'per_page' => ['nullable', 'int'],
            'statuses' => ['nullable','array'],
            'statuses.*' => ['string', DeviceStatus::ruleIn()],
            'has_history' => ['nullable', 'boolean'],
        ];
    }
}

