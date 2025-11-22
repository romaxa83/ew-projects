<?php

namespace App\Http\Requests\Api\OneC\Commercial;

use App\Http\Requests\BaseFormRequest;
use App\Models\Catalog\Products\Product;
use Illuminate\Validation\Rule;

class ProjectUnitsRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'data' => ['required', 'array'],
            'data.*.product_guid' => ['required', 'string', Rule::exists(Product::class, 'guid')],
            'data.*.serial_numbers' => ['required', 'array']
        ];
    }
}

