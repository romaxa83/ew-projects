<?php

namespace App\Http\Requests\Api\Vapi;

use Illuminate\Foundation\Http\FormRequest;

class RequestForClient extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_type' => ['required', 'string'],
            'caller_number' => ['nullable', 'string'],
            'client_full_name' => ['nullable', 'string'],
            'client_phone_number' => ['nullable', 'string'],
            'calling_number_is_client' => ['nullable', 'boolean'],
            'call_back_at' => ['nullable', 'string'],
            'pickup_location' => ['nullable', 'string'],
            'pickup_stairs' => ['nullable', 'string'],
            'delivery_location' => ['nullable', 'string'],
            'delivery_stairs' => ['nullable', 'string'],
            'additional' => ['nullable', 'string'],
        ];
    }
}
