<?php

namespace App\Http\Requests\Saas\Companies;

use App\Http\Requests\Saas\BaseSassRequest;
use App\Traits\Requests\OnlyValidateForm;

class CompanyAdminRequest extends BaseSassRequest
{
    use OnlyValidateForm;
    public function rules(): array
    {
        return [
            'email_search' => ['nullable', 'string', 'min:3'],
        ];
    }
}
