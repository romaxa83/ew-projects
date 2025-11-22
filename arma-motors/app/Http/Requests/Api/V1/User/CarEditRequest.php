<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;

class CarEditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number'  => ['nullable', 'string'],
            'vin'  => ['nullable', 'string'],
            'verify'  => ['required', 'boolean'],
            'name'  => ['nullable', 'string'],
            'year'  => ['nullable'],
            'model' => ['nullable', 'string'],
            'orderCar.0.statusPayment'  => ['nullable', 'integer'],
            'orderCar.0.sum'  => ['nullable', 'numeric'],
            'orderCar.0.sumDiscount'  => ['nullable', 'numeric'],
            'proxies'  => ['nullable', 'array'],
            'proxies.*.id'  => ['required', 'string'],
            'proxies.*.name'  => ['required', 'string'],
            'proxies.*.number'  => ['required', 'string'],
        ];
    }
}

