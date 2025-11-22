<?php

namespace App\Http\Requests\Users;

use App\Rules\CurrentPassword;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    use OnlyValidateForm;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', new CurrentPassword(), 'min:5', 'max:32'],
            'password' => [
                'required',
                'different:current_password',
                'min:8',
                'max:32',
                'regex:/^(?=.*[0-9]+.*)(?=.*[a-zA-Z]+.*)[0-9a-zA-Z]{8,}$/'
            ],
            'password_confirmation' => ['required', 'same:password', 'min:8', 'max:191']
        ];
    }

}
