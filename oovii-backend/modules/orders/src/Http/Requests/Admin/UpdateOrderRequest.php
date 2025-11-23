<?php

namespace WezomCms\Orders\Http\Requests\Admin;

class UpdateOrderRequest extends CreateOrderRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules['provider_id'] = 'nullable|int|exists:providers,id';
        $rules['QUANTITY.*'] = 'required|numeric|min:0';

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = parent::attributes();

        $attributes['QUANTITY.*'] = __('cms-orders::admin.orders.Quantity') . ' (:input)';

        return $attributes;
    }
}
