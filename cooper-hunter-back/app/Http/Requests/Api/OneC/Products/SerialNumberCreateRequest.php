<?php

namespace App\Http\Requests\Api\OneC\Products;

use App\Http\Requests\BaseFormRequest;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use Illuminate\Validation\Rule;

class SerialNumberCreateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'int', Rule::exists(Product::class, 'id')],
            'serial_number' => [
                'required',
                'string',
                Rule::unique(ProductSerialNumber::TABLE, 'serial_number')
            ],
        ];
    }
}
