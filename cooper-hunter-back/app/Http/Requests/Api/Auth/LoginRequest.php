<?php

namespace App\Http\Requests\Api\Auth;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Password;

class LoginRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email:filter'],
            'password' => [Password::required()],
        ];
    }
}
