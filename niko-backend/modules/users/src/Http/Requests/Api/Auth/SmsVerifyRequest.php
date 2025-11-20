<?php

namespace WezomCms\Users\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SmsVerifyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/','max:191'],
            'code' => ['required', 'string'],
            'deviceId' => ['required', 'string'],
            'dropCurrentSession' => ['required'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }
}
