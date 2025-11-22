<?php

namespace App\Http\Requests\Saas;

use App\Http\Requests\ForgotPasswordRequest;

class AdminForgotPasswordRequest extends ForgotPasswordRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:admins,email', $this->email(), 'max:191'],
        ];
    }
}
