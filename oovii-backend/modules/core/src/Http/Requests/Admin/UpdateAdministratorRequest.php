<?php

namespace WezomCms\Core\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdministratorRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|email|unique:administrators,email',
        ];

        if ($this->route('administrator')) {
            $rules['email'] .= ',' . $this->route('administrator');
            $rules['password'] = 'nullable|string|between:6,255|confirmed';
        }

        return $rules;
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
