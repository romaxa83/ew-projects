<?php

namespace WezomLaravel\Orders\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class NovaPoshtaCityWarehousesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return ['city_ref' => 'required|string'];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return ['city_ref.required' => __('cms-orders::site.checkout.Choose a locality from the list')];
    }
}
