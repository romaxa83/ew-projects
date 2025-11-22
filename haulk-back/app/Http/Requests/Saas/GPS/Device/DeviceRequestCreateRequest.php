<?php

namespace App\Http\Requests\Saas\GPS\Device;

use App\Http\Requests\Saas\BaseSassRequest;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use App\Rules\Users\UserBelongsCompany;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Validation\Rule;

class DeviceRequestCreateRequest extends BaseSassRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'qty' => ['required', 'integer'],
            'company_id' => ['required', 'integer', Rule::exists(Company::TABLE_NAME, 'id')],
            'user_email' => [
                'bail',
                'required',
                'email',
                Rule::exists(User::TABLE_NAME, 'email'),
                new UserBelongsCompany($this->company_id)
            ],
        ];
    }
}
