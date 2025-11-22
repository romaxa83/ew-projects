<?php

namespace App\Http\Requests\Api\OneC\Products;

use App\Http\Requests\BaseFormRequest;
use App\Models\Catalog\Products\Product;
use App\Permissions\Catalog\Products\DeletePermission;
use Illuminate\Validation\Rule;

class SerialNumbersDeleteRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(DeletePermission::KEY);
    }

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
}
