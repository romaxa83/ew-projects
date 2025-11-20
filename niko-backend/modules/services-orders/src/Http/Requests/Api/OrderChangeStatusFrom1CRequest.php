<?php

namespace WezomCms\ServicesOrders\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class OrderChangeStatusFrom1CRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ApplicationID' => ['required'],
            'ApplicationStatusID' => ['required'],
            'FinalOrderCost' => ['nullable'],
            'SparePartsDiscount' => ['nullable'],
            'ServicesDiscount' => ['nullable'],
            'PriceOrderCost' => ['nullable'],
            'СonfirmedDataTime' => ['nullable'],
        ];
    }

    public function attributes()
    {
        return [];
    }
}
