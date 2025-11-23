<?php

namespace WezomCms\Core\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'token' => __('cms-core::admin.administrators.Token'),
            'email' => __('cms-core::admin.administrators.E-mail'),
            'password' => __('cms-core::admin.administrators.Password'),
        ];
    }
}
