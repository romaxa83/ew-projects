<?php

namespace App\Http\Requests\Saas;

use App\Http\Requests\ResetPasswordRequest;

class AdminResetPasswordRequest extends ResetPasswordRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['email'] = ['required', 'email', 'exists:admins,email', $this->email(), 'max:191'];

        return $rules;
    }
}
