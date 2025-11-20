<?php

namespace WezomCms\ServicesOrders\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class OrderFreeTimeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'dealerId' => ['required', 'integer'],
            'type' => ['required', 'integer'],
            'timestamp' => ['required', 'integer'],
            'serviceId' => ['nullable', 'integer'],
            'modelId' => ['nullable', 'integer'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }
}
