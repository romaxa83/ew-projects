<?php

namespace App\Http\Requests\V2\Users;

class ProfileRequest extends \App\Http\Requests\Users\ProfileRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        unset($rules['full_name']);
        $rules['first_name'] = ['required', 'string', 'max:191', 'alpha_spaces'];
        $rules['last_name'] = ['required', 'string', 'max:191', 'alpha_spaces'];

        return $rules;
    }

    protected function prepareForValidation()
    {
    }
}
