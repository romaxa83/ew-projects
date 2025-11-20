<?php

namespace WezomCms\ServicesOrders\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class ServiceOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'service_id' => 'required|integer|exists:services,id',
            'username' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'city' => 'required|max:255',
            'message' => 'required|between:10,65535',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'service_id' => __('cms-services-orders::site.Service'),
            'username' => __('cms-services-orders::site.Name'),
            'phone' => __('cms-services-orders::site.Phone'),
            'email' => __('cms-services-orders::site.E-mail'),
            'city' => __('cms-services-orders::site.City'),
            'message' => __('cms-services-orders::site.Message'),
        ];
    }
}
