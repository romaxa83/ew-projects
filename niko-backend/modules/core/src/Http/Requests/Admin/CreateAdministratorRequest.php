<?php

namespace WezomCms\Core\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateAdministratorRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|email|unique:administrators,email',
            'password' => 'required|string|between:6,255|confirmed',
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
            'name' => __('cms-core::admin.administrators.Name'),
            'email' => __('cms-core::admin.administrators.E-mail'),
            'password' => __('cms-core::admin.administrators.Password'),
        ];
    }
}
