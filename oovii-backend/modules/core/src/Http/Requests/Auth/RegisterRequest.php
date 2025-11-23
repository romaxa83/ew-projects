<?php

namespace WezomCms\Core\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use WezomCms\Core\Rules\PhoneOrPhoneMask;
use WezomCms\Orders\Rules\SdekCity;
use WezomCms\Orders\Rules\SdekRegion;
use WezomCms\Providers\Models\Provider;

class RegisterRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'email' => ['required', 'string', 'max:255', 'email',
                Rule::unique(Provider::TABLE, 'email')->ignore($this->route('provider'))
            ],
            'phone' => ['required', new PhoneOrPhoneMask(),
                Rule::unique(Provider::TABLE, 'phone')->ignore($this->route('provider'))
            ],
            'region_code' => ['required', new SdekRegion()],
            'city_code' => ['required', new SdekCity((int) $this->get('region_code'))],
            'password' => 'required|string|min:8|confirmed',
            'company' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'email' => __('cms-core::admin.administrators.E-mail'),
            'password' => __('cms-core::admin.administrators.Password'),
            'phone' => __('cms-core::admin.administrators.Phone'),
            'region_code' => __('cms-orders::admin.courier.Region'),
            'city_code' => __('cms-orders::admin.courier.Locality'),
        ];
    }
}
