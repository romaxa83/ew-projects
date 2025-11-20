<?php

namespace WezomCms\ServicesOrders\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use WezomCms\Core\Rules\Phone;
use WezomCms\Users\Models\User;
use WezomCms\Users\Rules\EmailOrPhone;

class OrderRateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'serviceRating' => ['nullable', 'integer'],
            'orderRating' => ['nullable', 'integer'],
            'comment' => ['nullable', 'string'],
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
