<?php

namespace App\Http\Requests\Api\OneC\Products;

use App\Http\Requests\BaseFormRequest;
use App\Models\Catalog\Products\Product;
use App\Permissions\Catalog\Products\CreatePermission;
use Illuminate\Validation\Rule;

class SerialNumbersImportRequest extends BaseFormRequest
{
    public const PERMISSION = CreatePermission::KEY;

    public function rules(): array
    {
        return [
            'product_guid' => ['required', 'string', 'uuid', Rule::exists(Product::class, 'guid')],
            'serial_numbers' => [
                'required',
                'array',
                'min:1',
            ],
            'serial_numbers.*' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'product_guid.exists' => __('validation.exists') . " Given guid: " . $this->get('product_guid')
        ];
    }
}
