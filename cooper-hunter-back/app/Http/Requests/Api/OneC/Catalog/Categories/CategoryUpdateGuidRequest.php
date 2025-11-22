<?php

namespace App\Http\Requests\Api\OneC\Catalog\Categories;

use App\Http\Requests\Api\OneC\AbstractUpdateGuidRequest;
use App\Models\Catalog\Categories\Category;
use App\Permissions\Catalog\Categories\UpdatePermission;

class CategoryUpdateGuidRequest extends AbstractUpdateGuidRequest
{
    protected const MODEL = Category::class;

    public function authorize(): bool
    {
        return $this->user()->can(UpdatePermission::KEY);
    }
}
