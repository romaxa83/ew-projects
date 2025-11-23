<?php

namespace WezomCms\Providers\Http\Requests\Admin\Provider;

use WezomCms\Orders\Rules\SdekCity;
use WezomCms\Orders\Rules\SdekRegion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use WezomCms\Core\Rules\PhonePatterns;
use WezomCms\Providers\Models\Provider;

class CreateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'active' => 'required',
            'name' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'password' => 'required|string|between:' . config('cms.users.users.password_min_length') . ',255|confirmed',
            'email' => ['required', 'string', 'max:255', 'email',
                Rule::unique(Provider::TABLE, 'email')
                    ->ignore($this->route('provider')),
//                Rule::unique('administrators', 'email')
//                    ->ignore($this->route('provider'))
            ],
            'email_verified' => ['required'],
            'phone' => ['required', new PhonePatterns,
                Rule::unique(Provider::TABLE, 'phone')
                    ->ignore($this->route('provider'))
            ],
            'region_code' => ['required', new SdekRegion()],
            'city_code' => ['required', new SdekCity($this->get('region_code'))],
            'address' => 'required|string|max:255',
            'phone_verified' => ['required'],
        ];
    }

    public function attributes()
    {
        return [
            'active' => __('cms-providers::admin.Status'),
            'name' => __('cms-providers::admin.Name'),
            'email' => __('cms-users::admin.E-mail'),
            'password' => __('cms-users::admin.Password'),
            'phone' => __('cms-users::admin.Phone'),
            'address' => __('cms-providers::admin.company.Address'),
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => __('cms-users::admin.User with provided email already exists'),
            'phone.unique' => __('cms-users::admin.User with provided phone already exists'),
        ];
    }
}
