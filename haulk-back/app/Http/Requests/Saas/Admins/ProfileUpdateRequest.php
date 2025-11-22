<?php

namespace App\Http\Requests\Saas\Admins;

use App\Http\Requests\Saas\BaseSassRequest;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;

class ProfileUpdateRequest extends BaseSassRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function rules(): array
    {
        return [
            'full_name' => [
                'required',
                'string',
                'max:191',
                'alpha_spaces'
            ],
            'phone' => [
                'required',
                'string',
                $this->USAPhone(),
            ],
        ];
    }

}
