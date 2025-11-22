<?php

namespace App\Http\Requests\Saas\GPS\Device;

use App\Http\Requests\Saas\BaseSassRequest;
use App\Models\Saas\GPS\Device;
use App\Rules\Saas\Company\CompanyHasGpsSubscription;
use App\Rules\Saas\Company\CompanyIsCancelSubscription;
use App\Rules\Saas\Gps\CanAddDeviceToCompanyRule;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Validation\Rule;

class DeviceRequest extends BaseSassRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:30'],
            'imei' => ['required', 'string', 'max:15', Rule::unique(Device::TABLE_NAME, 'imei')],
            'device_id' => ['required', 'integer', Rule::unique(Device::TABLE_NAME, 'flespi_device_id')],
            'phone' => ['nullable', 'string', Rule::unique(Device::TABLE_NAME, 'phone')],
            'company_id' => [
                'bail',
                'nullable',
                'integer',
                new CompanyHasGpsSubscription(),
                new CanAddDeviceToCompanyRule(),
                new CompanyIsCancelSubscription(),
            ],
        ];
    }
}
