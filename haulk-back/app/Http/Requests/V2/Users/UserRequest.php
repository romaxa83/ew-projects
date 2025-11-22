<?php

namespace App\Http\Requests\V2\Users;

class UserRequest extends \App\Http\Requests\Users\UserRequest
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
        $this->transformPhoneAttribute('phone');

        if ($this->has('email')) {
            $this->merge(
                [
                    'email' => mb_convert_case($this->input('email'), MB_CASE_LOWER),
                ]
            );
        }
    }
}
