<?php

namespace App\Http\Requests\Saas\GPS\Device;

use App\Enums\Saas\GPS\Request\DeviceRequestStatus;
use App\Http\Requests\Saas\BaseSassRequest;
use App\Traits\Requests\OnlyValidateForm;

class DeviceRequestUpdateRequest extends BaseSassRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', DeviceRequestStatus::ruleIn()],
            'comment' => ['required_if:status,'.DeviceRequestStatus::CLOSED, 'string', 'max:280'],
        ];
    }
}
