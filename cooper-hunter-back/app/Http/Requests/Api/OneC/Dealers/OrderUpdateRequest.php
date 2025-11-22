<?php

namespace App\Http\Requests\Api\OneC\Dealers;

use App\Http\Requests\BaseFormRequest;

class OrderUpdateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string'],
            'term' => ['nullable', 'string'],
            'tax' => ['nullable', 'numeric'],
            'shipping_price' => ['nullable', 'numeric'],
            'total' => ['nullable', 'numeric'],
            'total_discount' => ['nullable', 'numeric'],
            'total_with_discount' => ['nullable', 'numeric'],
            'products' => ['nullable', 'array'],
            'products.*.guid' => ['required', 'string'],
            'products.*.discount' => ['required', 'numeric'],
            'products.*.discount_total' => ['nullable', 'numeric'],
            'products.*.qty' => ['required', 'numeric'],
            'products.*.total' => ['required', 'numeric'],
            'products.*.price' => ['required', 'numeric'],
            'products.*.description' => ['nullable', 'string'],
        ];
    }
}
