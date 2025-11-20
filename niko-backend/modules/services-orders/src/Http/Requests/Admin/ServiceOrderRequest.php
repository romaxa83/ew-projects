<?php

namespace WezomCms\ServicesOrders\Http\Requests\Admin;

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
            'read' => 'required',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'city' => 'required|max:255',
            'message' => 'required|string|max:65535',
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
            'service_id' => __('cms-services-orders::admin.Service'),
            'read' => __('cms-services-orders::admin.Status'),
            'name' => __('cms-services-orders::admin.Name'),
            'phone' => __('cms-services-orders::admin.Phone'),
            'email' => __('cms-services-orders::admin.E-mail'),
            'city' => __('cms-services-orders::admin.City'),
            'message' => __('cms-services-orders::admin.Message'),
        ];
    }
}
