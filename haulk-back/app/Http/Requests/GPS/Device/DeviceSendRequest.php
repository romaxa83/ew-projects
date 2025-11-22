<?php

namespace App\Http\Requests\GPS\Device;

use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class DeviceSendRequest extends FormRequest
{
    use OnlyValidateForm;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'qty' => ['required', 'integer'],
        ];
    }
}

