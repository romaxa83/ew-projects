<?php

namespace App\Http\Requests\Saas\Companies;

use App\Http\Requests\Saas\BaseSassRequest;
use App\Models\Saas\Company\Company;
use App\Traits\Requests\OnlyValidateForm;

class ChangeCompanyStatusRequest extends BaseSassRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'active' => 'required|boolean',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(
            [
                'active' => $this->boolean('active'),
            ]
        );
    }
}
