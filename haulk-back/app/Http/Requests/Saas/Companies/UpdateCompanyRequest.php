<?php

namespace App\Http\Requests\Saas\Companies;

use App\Http\Requests\Saas\BaseSassRequest;
use App\Models\Saas\Company\Company;
use App\Rules\Gps\DeviceSubscription\FieldForDeviceSubscriptionRule;
use App\Rules\Gps\DeviceSubscription\NextRateRule;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends BaseSassRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function rules(): array
    {
        $rule = [
            'address' => ['required', 'string', 'max:191'],
            'city' => ['required', 'string', 'max:255'],
            'state_id' => ['required', 'integer', 'exists:App\Models\Locations\State,id'],
            'zip' => ['required', 'string', 'min:3', 'max:10'],
            'phone' => ['required', 'string', $this->USAPhone(), 'max:191'],
            'phone_name' => ['nullable', 'string', 'max:255'],
            'phone_extension' => ['nullable', 'string', 'max:191'],
            'phones' => ['nullable', 'array'],
            'phones.*.name' => ['nullable', 'string', 'max:255'],
            'phones.*.number' => ['sometimes', 'string', $this->USAPhone(), 'max:191'],
            'phones.*.extension' => ['nullable', 'string', 'max:191'],
            'fax' => ['nullable', 'string', 'max:191'],
            'email' => [
                'required',
                'email',
                $this->email(),
                Rule::unique(Company::TABLE_NAME, 'email')->ignore($this->company->id),
            ],
            'website' => ['nullable', 'string', 'max:191'],
            'use_in_body_shop' => ['nullable', 'boolean'],
        ];

        if(!$this->company->isExclusivePlan()){
            $rule['next_rate'] = [
                'bail',
                'nullable',
                'numeric',
                'min:1',
                new FieldForDeviceSubscriptionRule($this->company),
                new NextRateRule($this->company),
            ];
        }

        return $rule;
    }
}
