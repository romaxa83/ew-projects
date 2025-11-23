<?php

namespace WezomCms\Providers\Http\Requests\Admin\Provider;

class UpdateRequest extends CreateRequest
{
    public function rules()
    {
        $rules = parent::rules();

        if ($this->route('provider')) {
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
