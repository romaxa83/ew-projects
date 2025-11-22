<?php

namespace App\Http\Requests\Api\OneC\Products;

use App\Dto\ModelGuidsDto;
use App\Http\Requests\Api\OneC\AbstractUpdateGuidRequest;
use App\Models\Catalog\Products\Product;
use App\Permissions\Catalog\Products\UpdatePermission;

class ProductUpdateGuidRequest extends AbstractUpdateGuidRequest
{
    protected const MODEL = Product::class;

    public function authorize(): bool
    {
        return $this->user()->can(UpdatePermission::KEY);
    }

    public function getDto(): ModelGuidsDto
    {
        return ModelGuidsDto::byArgs($this->validated());
    }
}
