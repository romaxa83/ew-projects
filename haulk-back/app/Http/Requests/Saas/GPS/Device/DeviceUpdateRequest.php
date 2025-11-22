<?php

namespace App\Http\Requests\Saas\GPS\Device;

use App\Http\Requests\Saas\BaseSassRequest;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Rules\Saas\Company\CompanyHasGpsSubscription;
use App\Rules\Saas\Company\CompanyIsCancelSubscription;
use App\Rules\Saas\Gps\CanAddDeviceToCompanyRule;
use App\Rules\Saas\Gps\DeviceCompanyUpdateRule;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Validation\Rule;

class DeviceUpdateRequest extends BaseSassRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:30'],
            'device_id' => ['nullable', 'integer',
                Rule::unique(Device::TABLE_NAME, 'flespi_device_id')
                    ->ignore($this->id)
            ],
            'phone' => ['nullable', 'string',
                Rule::unique(Device::TABLE_NAME, 'phone')
                    ->ignore($this->id)
            ],
            'imei' => ['required', 'string', 'max:15',
                Rule::unique(Device::TABLE_NAME, 'imei')
                    ->ignore($this->id)
            ],
            'company_id' => [
                'bail',
                'nullable',
                'integer',
                Rule::exists(Company::TABLE_NAME, 'id'),
                new DeviceCompanyUpdateRule($this->id),
                new CompanyHasGpsSubscription(),
                new CanAddDeviceToCompanyRule(),
                new CompanyIsCancelSubscription(),
            ],
        ];
    }
}
