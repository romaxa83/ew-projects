<?php

namespace WezomLaravel\Orders\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class NovaPoshtaCitiesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return ['query' => 'string|min:3'];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return ['query.min' => __('cms-orders::site.checkout.Enter at least 3 characters')];
    }
}
