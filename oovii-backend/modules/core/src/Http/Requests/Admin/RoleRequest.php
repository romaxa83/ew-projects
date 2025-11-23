<?php

namespace WezomCms\Core\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use WezomCms\Core\Models\Role;

class RoleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Role::TABLE, 'name')->ignore($this->route('role')),
            ],
            'permissions' => 'required|array',
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
            'name' => __('cms-core::admin.roles.Name'),
            'permissions' => __('cms-core::admin.roles.Permissions list'),
        ];
    }
}
