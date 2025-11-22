<?php

namespace App\Http\Requests\Api\OneC\Orders\Categories;

use App\Permissions\Orders\Categories\OrderCategoryUpdatePermission;

class OrderCategoryUpdateRequest extends OrderCategoryCreateRequest
{
    public const PERMISSION = OrderCategoryUpdatePermission::KEY;

    public function rules(): array
    {
        $rules = parent::rules();

        unset($rules['guid']);

        return $rules;
    }
}
