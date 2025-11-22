<?php

namespace App\Http\Requests\Api\OneC\Products;

use App\Models\Catalog\Products\Product;
use App\Permissions\Catalog\Products\UpdatePermission;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends ProductCreateRequest
{
    public const PERMISSION = UpdatePermission::KEY;

    public function rules(): array
    {
        $rules = parent::rules();

        unset($rules['slug'], $rules['guid']);

        $rules['slug'] = ['required', 'string', Rule::unique(Product::class, 'slug')->ignore($this->product)];

        return $rules;
    }
}
