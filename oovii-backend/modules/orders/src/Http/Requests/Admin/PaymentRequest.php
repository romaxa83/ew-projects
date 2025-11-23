<?php

namespace WezomCms\Orders\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class PaymentRequest extends FormRequest
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
                'icon' => ['nullable', 'mimetypes:image/jpeg,image/png,image/svg+xml', 'max:500'],
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
                'name' => __('cms-orders::admin.payments.Name'),
                'text' => __('cms-orders::admin.delivery-and-payment.Text'),
            ],
            [
                'published' => __('cms-core::admin.layout.Published'),
                'icon' => __('cms-orders::admin.delivery-and-payment.Icon'),
            ]
        );
    }
}
