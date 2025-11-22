<?php

namespace App\Http\Requests\Api\OneC\Dealers;

use App\Http\Requests\BaseFormRequest;
use App\Models\Companies\Company;
use App\Models\Companies\ShippingAddress;
use Illuminate\Validation\Rule;

class OrderCreateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'guid' => ['required', 'string'],
            'company_guid' => ['required', 'string', Rule::exists(Company::class, 'guid')],
            'shipping_address_id' => ['required', Rule::exists(ShippingAddress::class, 'id')],
            'delivery_type' => ['required', 'string'],
            'payment_type' => ['required', 'string'],
            'type' => ['nullable', 'string'],
            'po' => ['required', 'string'],
            'comment' => ['nullable', 'string'],
            'term' => ['nullable', 'string'],
            'tax' => ['nullable', 'numeric'],
            'shipping_price' => ['nullable', 'numeric'],
            'total' => ['nullable', 'numeric'],
            'total_discount' => ['nullable', 'numeric'],
            'total_with_discount' => ['nullable', 'numeric'],
            'products' => ['required', 'array'],
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
