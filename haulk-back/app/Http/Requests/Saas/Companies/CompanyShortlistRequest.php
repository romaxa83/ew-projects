<?php

namespace App\Http\Requests\Saas\Companies;

use App\Http\Requests\Saas\BaseSassRequest;
use App\Rules\BooleanRule;
use App\Traits\Requests\OnlyValidateForm;

class CompanyShortlistRequest extends BaseSassRequest
{
    use OnlyValidateForm;
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer'],
            'query' => ['nullable', 'string', 'min:3'],
            'gps_enabled' => ['nullable', new BooleanRule()],
        ];
    }
}
