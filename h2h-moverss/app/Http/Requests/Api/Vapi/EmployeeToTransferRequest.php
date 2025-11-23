<?php

namespace App\Http\Requests\Api\Vapi;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeToTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_type' => ['nullable', 'string'],
        ];
    }
}
