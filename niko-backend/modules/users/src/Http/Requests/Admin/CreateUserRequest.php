<?php

namespace WezomCms\Users\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Rules\PhoneOrPhoneMask;

class CreateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'active' => 'required',
            'name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'password' => 'required|string|between:' . config('cms.users.users.password_min_length') . ',255|confirmed',
            'email' => 'required_without:phone|nullable|string|max:255|email|unique:users,email,' . $this->route('user'),
            'phone' => [
                'nullable',
                'required_without:email',
                new PhoneOrPhoneMask(),
                'unique:users,phone,' . $this->route('user'),
            ],
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
            'active' => __('cms-users::admin.Status'),
            'name' => __('cms-users::admin.Name'),
            'surname' => __('cms-users::admin.Surname'),
            'email' => __('cms-users::admin.E-mail'),
            'password' => __('cms-users::admin.Password'),
            'phone' => __('cms-users::admin.Phone'),
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.unique' => __('cms-users::admin.User with provided email already exists'),
            'phone.unique' => __('cms-users::admin.User with provided phone already exists'),
        ];
    }
}
