<?php

namespace App\Http\Requests;

use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email', 'exists:users,email', $this->email(), 'max:191'],
            'password' => [
                'required',
                'min:8',
                'max:191',
                'regex:/^(?=.*[0-9]+.*)(?=.*[a-zA-Z]+.*)[0-9a-zA-Z\!\@\#\$\%\&\*\(\)\-\_\+\,\.\'\"\*]{8,}$/'
            ],
            'password_confirmation' => ['required', 'same:password', 'min:8', 'max:191']
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => "At least 8 characters long and contain letters, numbers, and symbols !#$%@*&"
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(
                [
                    'email' => mb_convert_case($this->input('email'), MB_CASE_LOWER),
                ]
            );
        }
    }
}
