<?php

namespace App\Http\Requests\Saas\Admins;

use App\Http\Requests\Saas\BaseSassRequest;
use App\Rules\CurrentPassword;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;

class ChangePasswordRequest extends BaseSassRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function rules(): array
    {
        return [
            'current_password' => ['required', new CurrentPassword(), 'min:5', 'max:32'],
            'password' => [
                'required',
                'different:current_password',
                'min:8',
                'max:32',
                $this->passwordRule()
            ],
            'password_confirmation' => ['required', 'same:password', 'min:8', 'max:191']
        ];
    }

}
