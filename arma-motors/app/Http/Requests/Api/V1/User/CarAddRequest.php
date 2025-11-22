<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;

class CarAddRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'brand' => ['required', 'string'],
            'model' => ['required', 'string'],
            'number'  => ['nullable', 'string'],
            'vin'  => ['required', 'string'],
            'year'  => ['required'],
            'yearDeal'  => ['nullable'],
            'owner'  => ['nullable', 'string'],
            'personal'  => ['required', 'boolean'],
            'buy'  => ['required', 'boolean'],
            'name'  => ['nullable', 'string'],
            'statusCar'  => ['required', 'boolean'],
            'orderCar'  => ['nullable', 'array'],
            'orderCar.orderNumber'  => ['nullable'],
            'orderCar.paymentStatusCar'  => ['nullable', 'integer'],
            'orderCar.sum'  => ['nullable', 'numeric'],
            'orderCar.sumDiscount'  => ['nullable', 'numeric'],
            'proxies'  => ['nullable', 'array'],
            'proxies.*.id'  => ['required', 'string'],
            'proxies.*.name'  => ['required', 'string'],
            'proxies.*.number'  => ['required', 'string'],
        ];
    }
}



