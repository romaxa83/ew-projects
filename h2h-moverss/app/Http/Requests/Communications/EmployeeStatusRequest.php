<?php

namespace App\Http\Requests\Communications;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read bool|null show_offline
 * @property-read bool|null reload_sip_status
 */

class EmployeeStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'show_offline' => ['nullable'],
            'reload_sip_status' => ['nullable'],
        ];
    }
}

