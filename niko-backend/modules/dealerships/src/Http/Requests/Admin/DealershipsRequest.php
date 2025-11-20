<?php

namespace WezomCms\Dealerships\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Http\Requests\ChangeStatus\RequiredIfMessageTrait;
use WezomCms\Core\Rules\PhoneMask;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class DealershipsRequest extends FormRequest
{
    use LocalizedRequestTrait;
    use RequiredIfMessageTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->localizeRules(
            [
                'name' => 'required|string|max:255',
                'text' => 'required|string',
                'address' => 'required|string',
                'services' => 'required|string',
            ],
            [
                'published' => 'nullable',
                'sort' => 'nullable|integer',
                'lat' => ['required','regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
                'lon' => ['required','regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
                'city_id' => 'required|integer',
                'brand_id' => 'nullable|integer',
                'email' => 'required|email',
                'site_link' => 'nullable|string',
                'phones' => 'nullable|array',
                'phones.phone.*' => 'nullable|string',
                'phones.desc.*' => 'nullable|string',
//                'phones.*' => 'required|string|max:255|distinct|regex:/^\+?[\d\s\(\)-]+$/',
//                'phone' => ['required', 'string', new PhoneMask()],
            ]
        );
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return $this->localizeAttributes(
            [
                'name' => __('cms-core::admin.layout.Name'),
                'text' => __('cms-dealerships::admin.Text'),
                'address' => __('cms-dealerships::admin.Address'),
                'services' => __('cms-dealerships::admin.Services'),
            ],
            [
                'sort' => __('cms-core::admin.layout.Position'),
                'published' => __('cms-core::admin.layout.Published'),
                'lat' => __('cms-regions::admin.latitude'),
                'lon' => __('cms-regions::admin.longitude'),
                'city_id' => __('cms-regions::admin.City'),
                'brand_id' => __('cms-cars::admin.Brand'),
                'email' => __('cms-dealerships::admin.Email'),
                'phones' => __('cms-dealerships::admin.Phones'),
                'site_link' => __('cms-dealerships::admin.Site link'),
            ]
        );
    }
}

