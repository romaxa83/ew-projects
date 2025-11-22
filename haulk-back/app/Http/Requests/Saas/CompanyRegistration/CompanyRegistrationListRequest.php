<?php

namespace App\Http\Requests\Saas\CompanyRegistration;

use App\Http\Requests\Saas\BaseSassRequest;
use App\Traits\Requests\OnlyValidateForm;

class CompanyRegistrationListRequest extends BaseSassRequest
{
    use OnlyValidateForm;

    public function orderBy(): string
    {
        return 'in:' . implode(
                ',',
                [
                    'id',
                    'created_at',
                ]
            );
    }
}
