<?php

namespace WezomCms\Orders\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddOrderItemRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'quantity' => 'required|numeric|min:0',
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::unique('order_items', 'product_id')->where('order_id', $this->route('id'))
            ],
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
            'quantity' => __('cms-orders::admin.orders.Quantity'),
            'product_id' => __('cms-orders::admin.orders.Product'),
        ];
    }
}
