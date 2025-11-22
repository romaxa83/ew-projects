<?php

namespace App\Http\Requests\Saas\Companies;

use App\Http\Requests\Saas\BaseSassRequest;
use App\Models\Locations\State;
use App\Traits\Requests\OnlyValidateForm;

class CompanyFilterRequest extends BaseSassRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return parent::rules() + [
            'active' => 'nullable|boolean',
            'query' => 'nullable|string',
        ];
    }

    public function orderBy(): string
    {
        return 'in:' . implode(
                ',',
                [
                    'name',
                    'created_at',
                    'registration_at',
                ]
            );
    }
}
