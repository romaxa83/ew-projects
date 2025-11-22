<?php

namespace App\Http\Requests\Saas\GPS\Device;

use App\Enums\Saas\GPS\Request\DeviceRequestStatus;
use App\Http\Requests\Saas\BaseSassRequest;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Validation\Rule;

class DeviceRequestFilterRequest extends BaseSassRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'max:' . config('admins.paginate.max_per_page')],
            'company_id' => ['nullable', 'string', Rule::exists(Company::TABLE_NAME, 'id')],
            'user_id' => ['nullable', 'string', Rule::exists(User::TABLE_NAME, 'id')],
            'status' => ['nullable', 'string', DeviceRequestStatus::ruleIn()],
        ];
    }
}
