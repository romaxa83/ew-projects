<?php

namespace App\Http\Requests\Orders;

use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;

class SignPublicBolRequest extends FormRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'min:2',
                'max:255'
            ],
            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:255'
            ],
            'signed_time' => [
                'required',
                'date_format:m/d/Y g:i A'
            ],
            'inspection_agree' => [
                'required',
                'boolean'
            ],
            'sign_file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png'
            ]
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'inspection_agree' => $this->boolean('inspection_agree')
        ]);
    }

}
