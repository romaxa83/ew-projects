<?php

namespace WezomCms\Orders\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class DeliveryVariantRequest extends FormRequest
{
    use LocalizedRequestTrait;

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
                'text' => 'nullable|string|max:16777215',
            ],
            [
                'published' => 'required',
                'image' => 'nullable|image',
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
                'name' => __('cms-orders::admin.delivery-and-payment.Name'),
                'text' => __('cms-orders::admin.delivery-and-payment.Text'),
            ],
            [
                'published' => __('cms-core::admin.layout.Published'),
                'image' => __('cms-orders::admin.delivery-and-payment.Image')
            ]
        );
    }
}
