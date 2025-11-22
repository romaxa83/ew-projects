<?php

namespace App\Http\Requests\Api\OneC\Warranty;

use App\Enums\Projects\Systems\WarrantyStatus;
use App\Http\Requests\BaseFormRequest;
use App\Permissions\Warranty\WarrantyRegistration\WarrantyRegistrationListPermission;

class WarrantyRegistrationListRequest extends BaseFormRequest
{
    public const PERMISSION = WarrantyRegistrationListPermission::KEY;

    public function rules(): array
    {
        return array_merge(
            $this->getPaginationRules(),
            [
                'warranty_status' => ['nullable', WarrantyStatus::ruleIn()],
                'id' => ['nullable'],
            ]
        );
    }
}
