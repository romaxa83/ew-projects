<?php

namespace App\Http\Requests\Api\Order;

use Illuminate\Foundation\Http\FormRequest;

class MobileEstimateUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'waiver_custom_reason' => ['nullable', 'string', 'max:1000'],
            'waiver_property_client_name' => ['nullable', 'string', 'max:255'],
            'waiver_oversize_client_name' => ['nullable', 'string', 'max:255'],
            'waiver_custom_client_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
