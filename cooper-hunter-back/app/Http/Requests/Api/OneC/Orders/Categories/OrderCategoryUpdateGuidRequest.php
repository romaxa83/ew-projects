<?php

namespace App\Http\Requests\Api\OneC\Orders\Categories;

use App\Http\Requests\Api\OneC\AbstractUpdateGuidRequest;
use App\Models\Orders\Categories\OrderCategory;
use App\Permissions\Orders\Categories\OrderCategoryUpdatePermission;

class OrderCategoryUpdateGuidRequest extends AbstractUpdateGuidRequest
{
    protected const MODEL = OrderCategory::class;

    public function authorize(): bool
    {
        return $this->user()->can(OrderCategoryUpdatePermission::KEY);
    }
}
