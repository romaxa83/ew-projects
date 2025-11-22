<?php

namespace App\Http\Requests\Saas\GPS\Device;

use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Http\Requests\Saas\BaseSassRequest;
use App\Models\Saas\Company\Company;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Validation\Rule;

class DeviceFilterRequest extends BaseSassRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'id' => ['nullable'],
            'page' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'max:' . config('admins.paginate.max_per_page')],
            'company_id' => ['nullable', 'string', Rule::exists(Company::TABLE_NAME, 'id')],
            'query' => ['nullable', 'string'],
            'status' => ['nullable', 'string', DeviceStatus::ruleIn()],
            'status_request' => ['nullable', 'string', DeviceRequestStatus::ruleIn()],
        ];
    }
}
