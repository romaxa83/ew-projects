<?php

namespace WezomCms\Users\Http\Requests\Admin;

class UpdateUserRequest extends CreateUserRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();

        if ($this->route('user')) {
            $rules['password'] = [
                'nullable',
                'string',
                'between:' . config('cms.users.users.password_min_length') . ',255',
                'confirmed'
            ];
        } else {
            unset($rules['password']);
        }

        return $rules;
    }
}
